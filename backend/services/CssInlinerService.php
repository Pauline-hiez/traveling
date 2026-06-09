<?php

/**
 * Inliner CSS simple pour email — applique les styles CSS directement aux éléments HTML
 * Aucune dépendance externe, PHP pur
 */
class CssInliner
{
    private $css = '';
    private $rules = [];
    private $mediaQueries = '';

    public function __construct(string $css = '')
    {
        // Stocke le CSS brut puis parse les règles
        $this->css = $css;
        $this->parseRules();
    }

    // Analyse le CSS en sélecteurs => règles de styles
    private function parseRules(): void
    {
        $css = preg_replace('#/\*.*?\*/#s', '', $this->css);

        if (preg_match('/@media\s[\s\S]*$/i', $css, $mediaMatch, PREG_OFFSET_CAPTURE)) {
            $mediaStart = $mediaMatch[0][1];
            $this->mediaQueries = trim(substr($css, $mediaStart));
            $css = trim(substr($css, 0, $mediaStart));
        }

        // Divise les blocs de règles
        if (preg_match_all('#([^{]+)\{([^}]+)\}#', $css, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $selector = trim($match[1]);
                $styles = trim($match[2]);

                if (empty($selector) || empty($styles)) {
                    continue;
                }

                if (!isset($this->rules[$selector])) {
                    $this->rules[$selector] = [];
                }
                $this->rules[$selector][] = $styles;
            }
        }
    }

    public function convert(string $html): string
    {
        if (empty($this->rules)) {
            return $this->appendMediaQueries($html);
        }

        foreach ($this->rules as $selector => $styleBlocks) {
            $styles = implode(';', $styleBlocks);
            if ($this->isSimpleSelector($selector)) {
                $html = $this->applyStylesToElements($html, $selector, $styles);
            }
        }

        $html = preg_replace('#<style[^>]*>.*?</style>#is', '', $html);
        $html = $this->appendMediaQueries($html);
        return $html;
    }

    private function appendMediaQueries(string $html): string
    {
        if (trim($this->mediaQueries) === '') {
            return $html;
        }

        $styleTag = "<style type=\"text/css\">\n" . $this->mediaQueries . "\n</style>";
        if (stripos($html, '<head>') !== false) {
            return preg_replace('#</head>#i', $styleTag . "\n<head>", $html, 1);
        }
        return $styleTag . $html;
    }

    private function isSimpleSelector(string $selector): bool
    {
        return !preg_match('#[\>\+\~:]#', $selector);
    }

    private function applyStylesToElements(string $html, string $selector, string $styles): string
    {
        $styles = $this->normalizeStyles($styles);

        if (str_starts_with($selector, '.')) {
            $className = substr($selector, 1);
            $html = preg_replace_callback(
                '#<([a-z]+)([^>]*?)class=["\']([^"\']*' . preg_quote($className) . '[^"\']*)["\']([^>]*)>#i',
                function ($m) use ($styles) {
                    $existingStyle = '';
                    if (preg_match('#style=["\']([^"\']*)["\']#i', $m[0], $styleMatch)) {
                        $existingStyle = trim($styleMatch[1], '; ') . ';';
                    }
                    $newStyle = $existingStyle . $styles;
                    $tag = preg_replace('#style=["\'][^"\']*["\']#i', '', $m[0]);
                    return preg_replace('#>#', ' style="' . htmlspecialchars($newStyle) . '">', $tag, 1);
                },
                $html
            );
        } elseif (str_starts_with($selector, '#')) {
            $id = substr($selector, 1);

            $html = preg_replace_callback(
                '#<([a-z]+)([^>]*?)id=["\']' . preg_quote($id) . '["\']([^>]*)>#i',
                function ($m) use ($styles) {
                    $existingStyle = '';
                    if (preg_match('#style=["\']([^"\']*)["\']#i', $m[0], $styleMatch)) {
                        $existingStyle = trim($styleMatch[1], '; ') . ';';
                    }
                    $newStyle = $existingStyle . $styles;
                    $tag = preg_replace('#style=["\'][^"\']*["\']#i', '', $m[0]);
                    return preg_replace('#>#', ' style="' . htmlspecialchars($newStyle) . '">', $tag, 1);
                },
                $html
            );
        } else {
            $tag = strtolower($selector);
            $html = preg_replace_callback(
                '#<' . preg_quote($tag) . '([^>]*)>#i',
                function ($m) use ($styles, $tag) {
                    $attrs = $m[1];
                    $existingStyle = '';
                    if (preg_match('#style=["\']([^"\']*)["\']#i', $attrs, $styleMatch)) {
                        $existingStyle = trim($styleMatch[1], '; ') . ';';
                    }
                    $newStyle = $existingStyle . $styles;
                    $newAttrs = preg_replace('#style=["\'][^"\']*["\']#i', '', $attrs);
                    return '<' . $tag . $newAttrs . ' style="' . htmlspecialchars($newStyle) . '">';
                },
                $html
            );
        }
        return $html;
    }

    private function normalizeStyles(string $styles): string
    {
        $styles = preg_replace('#\s+#', ' ', $styles);
        $styles = preg_replace('#;\s*$#', '', $styles);
        return trim($styles);
    }
}
