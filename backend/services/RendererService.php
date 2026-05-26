<?php

namespace Services;

class Renderer
{
    protected array $params = [];
    protected array $css = [];
    protected array $scripts = [];

    public function addParams(string $key, $value): void
    {
        // Ajoute un paramètre unique
        $this->params[$key] = $value;
    }

    public function addParamsArray(array $params): void
    {
        // Fusionne plusieurs paramètres
        $this->params = array_merge($this->params, $params);
    }

    public function addCss(string $href): void
    {
        // Ajoute le CSS à la page
        $this->css[] = $href;
    }

    public function addScripts(string $src): void
    {
        // Ajoute les scripts à la page
        $this->scripts[] = $src;
    }

    public function render(string $view, array $args = []): string
    {
        // Construit le chemin vers la vue
        $viewFile = VIEWS . $view . '.php';
        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View not found: $viewFile");
        }

        // Combine les params et expose au layout
        $params = array_merge($this->params, $args);

        // Explose les helpers à la vue/layout
        $css = $this->css;
        $scripts = $this->scripts;

        extract($params, EXTR_SKIP);

        // Capture le rendu
        ob_start();
        require COMPONENTS . 'layout.php';
        return ob_get_clean();
    }
}
