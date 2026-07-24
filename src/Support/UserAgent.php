<?php

namespace Ipatco\FilamentProfile\Support;

class UserAgent
{
    public function __construct(
        protected string $userAgent,
    ) {}

    public static function from(?string $userAgent): static
    {
        return new static($userAgent ?? '');
    }

    public function isDesktop(): bool
    {
        return ! preg_match('/mobile|android|iphone|ipad|ipod|blackberry|opera mini|iemobile/i', $this->userAgent);
    }

    public function platform(): ?string
    {
        return match (true) {
            str_contains($this->userAgent, 'Windows') => 'Windows',
            str_contains($this->userAgent, 'iPhone'),
            str_contains($this->userAgent, 'iPad'),
            str_contains($this->userAgent, 'iPod') => 'iOS',
            str_contains($this->userAgent, 'Macintosh'),
            str_contains($this->userAgent, 'Mac OS') => 'macOS',
            str_contains($this->userAgent, 'Android') => 'Android',
            str_contains($this->userAgent, 'Linux') => 'Linux',
            default => null,
        };
    }

    public function browser(): ?string
    {
        return match (true) {
            str_contains($this->userAgent, 'Edg/') => 'Edge',
            str_contains($this->userAgent, 'OPR/'),
            str_contains($this->userAgent, 'Opera') => 'Opera',
            str_contains($this->userAgent, 'Chrome'),
            str_contains($this->userAgent, 'CriOS') => 'Chrome',
            str_contains($this->userAgent, 'Firefox'),
            str_contains($this->userAgent, 'FxiOS') => 'Firefox',
            str_contains($this->userAgent, 'Safari') => 'Safari',
            default => null,
        };
    }
}
