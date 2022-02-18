<?php

namespace Cosmic\Bundle\Common;

use Cosmic\Core\Types\JSON;
use Cosmic\FileSystem\Paths\File;

class Language
{
    private static array $translations;

    public static function setupLanguage(): void
    {
        Language::$translations = JSON::from(new File("app/Translations.json"))->toArray();
        if (session("lang") == "") {
            session("lang", "en");
        }
    }

    public static function getLanguage(): string
    {
        Language::setupLanguage();
        return session("lang");
    }

    public static function getString(string $key): string
    {
        Language::setupLanguage();
        foreach (Language::$translations as $translation) {
            if ($translation['id'] == $key) {
                return $translation[session("lang")];
            }
        }
        return '';
    }
}
