<?php

namespace Framework;

use Framework\Views;

class Controller {
    /**
     * Render blade engine template from controller
     *
     * @param string $name The blade engine template name
     * @param array $params The params to pass in template
     * @return string
     */
    public function view(string $name, array $params = array()): string {
        return Views::renderTemplate($name, $params);
    }
}