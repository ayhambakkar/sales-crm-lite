<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    /**
     * Render a view template inside the shared application layout.
     *
     * The view file is located at:  src/Views/{$view}.php
     * The layout file is located at: src/Views/layouts/app.php
     *
     * Inside the layout, the rendered view is available as $content.
     * All entries in $data are extracted as local variables in the view.
     */
    protected function render(string $view, array $data = [], string $layout = 'layouts/app'): void
    {
        $viewPath = APP_ROOT . '/src/Views/' . $view . '.php';

        if (! file_exists($viewPath)) {
            $this->abort(404);
        }

        // Capture the inner view output
        extract($data, EXTR_SKIP);
        ob_start();
        include $viewPath;
        $content = ob_get_clean();

        // Wrap in layout (title can be passed via $data['title'])
        $title      = $data['title'] ?? Config::get('APP_NAME', 'Sales CRM Lite');
        $layoutPath = APP_ROOT . '/src/Views/' . $layout . '.php';

        if (file_exists($layoutPath)) {
            include $layoutPath;
        } else {
            echo $content;
        }
    }

    /**
     * Send a 302 redirect and stop execution.
     */
    protected function redirect(string $url): never
    {
        header('Location: ' . $url, true, 302);
        exit();
    }

    /**
     * Render an error page and stop execution.
     */
    protected function abort(int $code = 404): never
    {
        http_response_code($code);

        $view = APP_ROOT . '/src/Views/errors/' . $code . '.php';

        if (file_exists($view)) {
            include $view;
        } else {
            echo $code . ' — Error';
        }

        exit();
    }

    /**
     * Send a JSON response and stop execution.
     * Useful for AJAX endpoints.
     */
    protected function json(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit();
    }

    /**
     * Validate an array of fields against simple rules.
     *
     * Supported rules (pipe-separated): required | max:N | min:N | email | numeric
     *
     * Returns an array of error messages keyed by field name.
     * An empty array means validation passed.
     *
     * @param  array<string, string>  $rules  ['field' => 'required|max:100']
     * @param  array<string, mixed>   $data   Typically $_POST
     * @return array<string, string>
     */
    protected function validate(array $rules, array $data): array
    {
        $errors = [];

        foreach ($rules as $field => $ruleString) {
            $value     = trim((string) ($data[$field] ?? ''));
            $ruleList  = explode('|', $ruleString);
            $label     = ucfirst(str_replace('_', ' ', $field));

            foreach ($ruleList as $rule) {
                if ($rule === 'required' && $value === '') {
                    $errors[$field] = "{$label} is required.";
                    break;
                }

                if ($rule === 'email' && $value !== '' && ! filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = "{$label} must be a valid email address.";
                    break;
                }

                if ($rule === 'numeric' && $value !== '' && ! is_numeric($value)) {
                    $errors[$field] = "{$label} must be a number.";
                    break;
                }

                if (str_starts_with($rule, 'max:')) {
                    $max = (int) substr($rule, 4);
                    if (mb_strlen($value) > $max) {
                        $errors[$field] = "{$label} may not exceed {$max} characters.";
                        break;
                    }
                }

                if (str_starts_with($rule, 'min:')) {
                    $min = (int) substr($rule, 4);
                    if ($value !== '' && mb_strlen($value) < $min) {
                        $errors[$field] = "{$label} must be at least {$min} characters.";
                        break;
                    }
                }
            }
        }

        return $errors;
    }
}
