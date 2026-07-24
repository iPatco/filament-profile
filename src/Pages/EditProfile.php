<?php

namespace Ipatco\FilamentProfile\Pages;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Actions\Action;
use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Exceptions\Halt;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Ipatco\FilamentProfile\Forms\ProfileInformationForm;
use Ipatco\FilamentProfile\Support\UserAgent;
use SensitiveParameter;
use Throwable;

/**
 * @property-read Schema $form
 * @property-read Schema $passwordForm
 */
class EditProfile extends BaseEditProfile
{
    protected static bool $shouldRegisterNavigation = false;

    /**
     * @var array<string, mixed> | null
     */
    public ?array $passwordData = [];

    public function getView(): string
    {
        return 'filament-profile::pages.edit-profile';
    }

    public function mount(): void
    {
        $this->fillForm();
        $this->fillPasswordForm();
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema
            ->inlineLabel(false)
            ->model($this->getUser())
            ->operation('edit')
            ->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('filament-profile::profile.sections.profile.title'))
                    ->description(__('filament-profile::profile.sections.profile.description'))
                    ->aside()
                    ->schema($this->getProfileInformationFormComponents())
                    ->footer([
                        Actions::make([
                            $this->getSaveProfileFormAction(),
                        ])
                            ->alignment(Alignment::Start)
                            ->key('profile-form-actions'),
                    ]),
            ]);
    }

    /**
     * Get the components rendered in the Profile information section.
     *
     * Resolved from the class configured in `filament-profile.profile_information_form`.
     * Publish and edit that class with `php artisan filament-profile:install`.
     *
     * @return array<Component|Action|\Filament\Actions\ActionGroup>
     */
    public function getProfileInformationFormComponents(): array
    {
        $class = $this->getProfileInformationFormClass();

        return $class::configure($this);
    }

    /**
     * @return class-string<ProfileInformationForm>
     */
    public function getProfileInformationFormClass(): string
    {
        $class = config('filament-profile.profile_information_form', ProfileInformationForm::class);

        if (is_string($class) && class_exists($class) && method_exists($class, 'configure')) {
            /** @var class-string<ProfileInformationForm> $class */
            return $class;
        }

        return ProfileInformationForm::class;
    }

    /**
     * Name field for the Profile information section.
     */
    public function getNameFormComponent(): Component
    {
        return parent::getNameFormComponent();
    }

    /**
     * Email field for the Profile information section.
     */
    public function getEmailFormComponent(): Component
    {
        return parent::getEmailFormComponent();
    }

    /**
     * Current password field, shown when the email address changes.
     */
    public function getCurrentPasswordFormComponent(): Component
    {
        return parent::getCurrentPasswordFormComponent();
    }

    public function defaultPasswordForm(Schema $schema): Schema
    {
        return $schema
            ->inlineLabel(false)
            ->model($this->getUser())
            ->operation('edit')
            ->statePath('passwordData');
    }

    public function passwordForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('filament-profile::profile.sections.password.title'))
                    ->description(__('filament-profile::profile.sections.password.description'))
                    ->aside()
                    ->schema([
                        $this->getPasswordUpdateCurrentPasswordFormComponent(),
                        $this->getPasswordUpdatePasswordFormComponent(),
                        $this->getPasswordUpdateConfirmationFormComponent(),
                    ])
                    ->footer([
                        Actions::make([
                            $this->getSavePasswordFormAction(),
                        ])
                            ->alignment(Alignment::Start)
                            ->key('password-form-actions'),
                    ]),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->gap(false)
            ->components([
                $this->getProfileFormContentComponent(),
                $this->getPasswordFormContentComponent(),
                $this->getBrowserSessionsSection(),
                $this->getDeleteAccountSection(),
            ]);
    }

    public function getProfileFormContentComponent(): Component
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('updateProfileInformation');
    }

    public function getPasswordFormContentComponent(): Component
    {
        return Form::make([EmbeddedSchema::make('passwordForm')])
            ->id('passwordForm')
            ->livewireSubmitHandler('updatePassword');
    }

    public function getBrowserSessionsSection(): Component
    {
        return Section::make(__('filament-profile::profile.sections.sessions.title'))
            ->description(__('filament-profile::profile.sections.sessions.description'))
            ->aside()
            ->visible(fn (): bool => $this->canManageBrowserSessions())
            ->schema([
                View::make('filament-profile::components.browser-sessions')
                    ->viewData(fn (): array => [
                        'sessions' => $this->getBrowserSessions(),
                    ]),
            ])
            ->footer([
                Actions::make([
                    $this->getLogoutOtherBrowserSessionsAction(),
                ])
                    ->alignment(Alignment::Start)
                    ->key('browser-sessions-actions'),
            ]);
    }

    public function canManageBrowserSessions(): bool
    {
        return config('session.driver') === 'database';
    }

    /**
     * @return Collection<int, object{
     *     id: string,
     *     agent: UserAgent,
     *     ip_address: string|null,
     *     is_current_device: bool,
     *     last_active: string
     * }>
     */
    public function getBrowserSessions(): Collection
    {
        if (! $this->canManageBrowserSessions()) {
            return collect();
        }

        $user = $this->getUser();

        return collect(
            DB::connection(config('session.connection'))
                ->table(config('session.table', 'sessions'))
                ->where('user_id', $user->getAuthIdentifier())
                ->orderByDesc('last_activity')
                ->get()
        )->map(function (object $session): object {
            return (object) [
                'id' => $session->id,
                'agent' => UserAgent::from($session->user_agent),
                'ip_address' => $session->ip_address,
                'is_current_device' => $session->id === $this->getCurrentSessionId(),
                'last_active' => Carbon::createFromTimestamp($session->last_activity)->diffForHumans(),
            ];
        });
    }

    public function logoutBrowserSession(string $sessionId): void
    {
        if (! $this->canManageBrowserSessions()) {
            return;
        }

        if ($sessionId === $this->getCurrentSessionId()) {
            return;
        }

        DB::connection(config('session.connection'))
            ->table(config('session.table', 'sessions'))
            ->where('user_id', $this->getUser()->getAuthIdentifier())
            ->where('id', $sessionId)
            ->delete();

        Notification::make()
            ->success()
            ->title(__('filament-profile::profile.notifications.browser_session_logged_out'))
            ->send();
    }

    protected function getCurrentSessionId(): ?string
    {
        try {
            return session()->getId();
        } catch (Throwable) {
            return null;
        }
    }

    public function getDeleteAccountSection(): Component
    {
        return Section::make(__('filament-profile::profile.sections.delete.title'))
            ->description(__('filament-profile::profile.sections.delete.description'))
            ->aside()
            ->schema([
                Callout::make(__('filament-profile::profile.sections.delete.warning.heading'))
                    ->description(__('filament-profile::profile.sections.delete.warning.description'))
                    ->danger(),
            ])
            ->footer([
                $this->getDeleteAccountAction(),
            ]);
    }

    protected function fillPasswordForm(): void
    {
        $this->passwordForm->fill();
    }

    public function updateProfileInformation(): void
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return;
        }

        try {
            $this->beginDatabaseTransaction();

            $this->callHook('beforeValidate');

            $data = $this->form->getState();

            $this->callHook('afterValidate');

            $data = $this->mutateFormDataBeforeSave($data);

            $this->callHook('beforeSave');

            $this->handleRecordUpdate($this->getUser(), $data);

            $this->callHook('afterSave');
        } catch (Halt $exception) {
            $exception->shouldRollbackDatabaseTransaction()
                ? $this->rollBackDatabaseTransaction()
                : $this->commitDatabaseTransaction();

            return;
        } catch (Throwable $exception) {
            $this->rollBackDatabaseTransaction();

            throw $exception;
        }

        $this->commitDatabaseTransaction();

        $this->getProfileUpdatedNotification()?->send();
    }

    public function updatePassword(): void
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return;
        }

        try {
            $this->beginDatabaseTransaction();

            $data = $this->passwordForm->getState();

            $this->getUser()->forceFill([
                'password' => $data['password'],
            ])->save();
        } catch (Halt $exception) {
            $exception->shouldRollbackDatabaseTransaction()
                ? $this->rollBackDatabaseTransaction()
                : $this->commitDatabaseTransaction();

            return;
        } catch (Throwable $exception) {
            $this->rollBackDatabaseTransaction();

            throw $exception;
        }

        $this->commitDatabaseTransaction();

        if (request()->hasSession()) {
            request()->session()->put([
                'password_hash_' . Filament::getAuthGuard() => $data['password'],
            ]);
        }

        $this->fillPasswordForm();

        $this->getPasswordUpdatedNotification()?->send();
    }

    protected function getSaveProfileFormAction(): Action
    {
        return Action::make('updateProfileInformation')
            ->label(__('filament-profile::profile.sections.profile.actions.save'))
            ->submit('updateProfileInformation')
            ->keyBindings(['mod+s']);
    }

    protected function getSavePasswordFormAction(): Action
    {
        return Action::make('updatePassword')
            ->label(__('filament-profile::profile.sections.password.actions.save'))
            ->submit('updatePassword');
    }

    protected function getDeleteAccountAction(): Action
    {
        return Action::make('deleteAccount')
            ->label(__('filament-profile::profile.sections.delete.actions.delete'))
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading(__('filament-profile::profile.sections.delete.modal.heading'))
            ->modalDescription(__('filament-profile::profile.sections.delete.modal.description'))
            ->modalSubmitActionLabel(__('filament-profile::profile.sections.delete.actions.delete'))
            ->form([
                TextInput::make('password')
                    ->label(__('filament-profile::profile.fields.password'))
                    ->password()
                    ->revealable(filament()->arePasswordsRevealable())
                    ->required()
                    ->currentPassword(guard: Filament::getAuthGuard()),
            ])
            ->action(function (): void {
                $this->deleteAccount();
            });
    }

    protected function getLogoutOtherBrowserSessionsAction(): Action
    {
        return Action::make('logoutOtherBrowserSessions')
            ->label(__('filament-profile::profile.sections.sessions.actions.logout_others'))
            ->color('gray')
            ->requiresConfirmation()
            ->modalHeading(__('filament-profile::profile.sections.sessions.modal.heading'))
            ->modalDescription(__('filament-profile::profile.sections.sessions.modal.description'))
            ->modalSubmitActionLabel(__('filament-profile::profile.sections.sessions.actions.logout_others'))
            ->form([
                TextInput::make('password')
                    ->label(__('filament-profile::profile.fields.password'))
                    ->password()
                    ->revealable(filament()->arePasswordsRevealable())
                    ->required()
                    ->currentPassword(guard: Filament::getAuthGuard()),
            ])
            ->action(function (array $data): void {
                $this->logoutOtherBrowserSessions($data['password']);
            });
    }

    public function logoutOtherBrowserSessions(#[SensitiveParameter] string $password): void
    {
        if (! Hash::check($password, $this->getUser()->getAuthPassword())) {
            throw ValidationException::withMessages([
                'password' => [__('validation.current_password')],
            ]);
        }

        /** @var StatefulGuard $guard */
        $guard = Filament::auth();

        $guard->logoutOtherDevices($password);

        $this->deleteOtherBrowserSessionRecords();

        if (request()->hasSession()) {
            request()->session()->put([
                'password_hash_' . Filament::getAuthGuard() => $this->getUser()->getAuthPassword(),
            ]);
        }

        Notification::make()
            ->success()
            ->title(__('filament-profile::profile.notifications.other_browser_sessions_logged_out'))
            ->send();
    }

    protected function deleteOtherBrowserSessionRecords(): void
    {
        if (! $this->canManageBrowserSessions()) {
            return;
        }

        DB::connection(config('session.connection'))
            ->table(config('session.table', 'sessions'))
            ->where('user_id', $this->getUser()->getAuthIdentifier())
            ->where('id', '!=', $this->getCurrentSessionId())
            ->delete();
    }

    protected function deleteAccount(): void
    {
        $user = $this->getUser();

        Filament::auth()->logout();

        $user->delete();

        session()->invalidate();
        session()->regenerateToken();

        $this->redirect(Filament::getLoginUrl());
    }

    protected function getPasswordUpdateCurrentPasswordFormComponent(): Component
    {
        return TextInput::make('currentPassword')
            ->label(__('filament-profile::profile.fields.current_password'))
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->required()
            ->currentPassword(guard: Filament::getAuthGuard())
            ->autocomplete('current-password')
            ->dehydrated(false);
    }

    protected function getPasswordUpdatePasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label(__('filament-profile::profile.fields.password'))
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->required()
            ->rule(Password::default())
            ->autocomplete('new-password')
            ->dehydrated(fn (#[SensitiveParameter] $state): bool => filled($state))
            ->dehydrateStateUsing(fn (#[SensitiveParameter] $state): string => Hash::make($state))
            ->same('passwordConfirmation');
    }

    protected function getPasswordUpdateConfirmationFormComponent(): Component
    {
        return TextInput::make('passwordConfirmation')
            ->label(__('filament-profile::profile.fields.password_confirmation'))
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->required()
            ->autocomplete('new-password')
            ->dehydrated(false);
    }

    protected function getProfileUpdatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title(__('filament-profile::profile.notifications.profile_updated'));
    }

    protected function getPasswordUpdatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title(__('filament-profile::profile.notifications.password_updated'));
    }

    /**
     * @deprecated Use updateProfileInformation() instead.
     */
    public function save(): void
    {
        $this->updateProfileInformation();
    }
}
