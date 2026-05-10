<?php
namespace event;

class AIResponseEvent extends \pocketmine\event\plugin\PluginEvent {
    public static $handlerList = null;
    private $prompt;
    private $response;
    private $error;

    public function __construct(\Loader $plugin, $prompt, $response, $error) {
        parent::__construct($plugin);
        $this->prompt = $prompt;
        $this->response = $response;
        $this->error = $error;
    }

    public function getPrompt() {
        return $this->prompt;
    }

    public function getResponse() {
        return $this->response;
    }

    public function getError() {
        return $this->error;
    }

    public function hasError() {
        return $this->error !== null;
    }
}
