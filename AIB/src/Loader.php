<?php
use pocketmine\plugin\PluginBase;
use model\OpenRouterModel;
use model\MistralModel;
use model\GroqModel;
use model\TogetherModel;
use model\GeminiModel;
use model\ChatGPTModel;

class Loader extends PluginBase {
    private static $instance;
    private $modelRegistry = [];
    private $config;
    private $callbacks = [];
    private $sessions = [];
    private $activeModel = null;

    public function onEnable() {
        self::$instance = $this;
        $this->saveDefaultConfig();
        $this->config = $this->getConfig();
        $this->registerBuiltinModels();
        $this->getServer()->getPluginManager()->registerEvents(new \event\ChatListener($this), $this);
        $this->getServer()->getCommandMap()->register("aib", new \command\AIBCommand($this));
    }

    public static function getInstance() {
        return self::$instance;
    }

    public function storeCallback($taskId, $callback) {
        $this->callbacks[$taskId] = $callback;
    }

    public function fetchCallback($taskId) {
        if(isset($this->callbacks[$taskId])) {
            $cb = $this->callbacks[$taskId];
            unset($this->callbacks[$taskId]);
            return $cb;
        }
        return null;
    }

    public function registerBuiltinModels() {
        $mods = $this->config->get("models", []);
        if(isset($mods["openrouter"]) && isset($mods["openrouter"]["api_key"]) && $mods["openrouter"]["api_key"] !== "") {
            $this->registerModel(new OpenRouterModel($mods["openrouter"]["api_key"], isset($mods["openrouter"]["model"]) ? $mods["openrouter"]["model"] : "meta-llama/llama-3.1-8b-instruct:free"));
        }
        if(isset($mods["groq"]) && isset($mods["groq"]["api_key"]) && $mods["groq"]["api_key"] !== "") {
            $this->registerModel(new GroqModel($mods["groq"]["api_key"], isset($mods["groq"]["model"]) ? $mods["groq"]["model"] : "llama-3.1-8b-instant"));
        }
        if(isset($mods["together"]) && isset($mods["together"]["api_key"]) && $mods["together"]["api_key"] !== "") {
            $this->registerModel(new TogetherModel($mods["together"]["api_key"], isset($mods["together"]["model"]) ? $mods["together"]["model"] : "meta-llama/Llama-3.2-11B-Vision-Instruct-Turbo"));
        }
        if(isset($mods["mistral"]) && isset($mods["mistral"]["api_key"]) && $mods["mistral"]["api_key"] !== "") {
            $this->registerModel(new MistralModel($mods["mistral"]["api_key"], isset($mods["mistral"]["model"]) ? $mods["mistral"]["model"] : "mistral-small-latest"));
        }
        if(isset($mods["gemini"]) && isset($mods["gemini"]["api_key"]) && $mods["gemini"]["api_key"] !== "") {
            $this->registerModel(new GeminiModel($mods["gemini"]["api_key"], isset($mods["gemini"]["model"]) ? $mods["gemini"]["model"] : "gemini-2.5-flash"));
        }
        if(isset($mods["chatgpt"]) && isset($mods["chatgpt"]["api_key"]) && $mods["chatgpt"]["api_key"] !== "") {
            $this->registerModel(new ChatGPTModel($mods["chatgpt"]["api_key"], isset($mods["chatgpt"]["model"]) ? $mods["chatgpt"]["model"] : "gpt-4o-mini"));
        }
    }

    public function registerModel(\model\AIModel $model) {
        $this->modelRegistry[$model->getModelId()] = $model;
        $this->getLogger()->info("Registered AI model: " . $model->getModelId());
    }

    public function getModel($modelId) {
        if(isset($this->modelRegistry[$modelId])) {
            return $this->modelRegistry[$modelId];
        }
        return null;
    }

    public function getDefaultModel() {
        if($this->activeModel !== null && isset($this->modelRegistry[$this->activeModel])) {
            return $this->modelRegistry[$this->activeModel];
        }
        $default = $this->config->get("default_model", null);
        if($default !== null && isset($this->modelRegistry[$default])) {
            return $this->modelRegistry[$default];
        }
        if(count($this->modelRegistry) > 0) {
            $keys = array_keys($this->modelRegistry);
            return $this->modelRegistry[$keys[0]];
        }
        return null;
    }

    public function getRegisteredModels() {
        return $this->modelRegistry;
    }

    public function setActiveModel($modelId) {
        $this->activeModel = $modelId;
    }

    public function getActiveModelId() {
        return $this->activeModel;
    }

    public function setModelKey($provider, $apiKey) {
        $mods = $this->config->get("models", []);
        if(!isset($mods[$provider])) {
            return false;
        }
        $mods[$provider]["api_key"] = $apiKey;
        $this->config->set("models", $mods);
        $this->config->save();
        $modelName = isset($mods[$provider]["model"]) ? $mods[$provider]["model"] : "";
        $model = null;
        switch($provider) {
            case "openrouter": $model = new OpenRouterModel($apiKey, $modelName !== "" ? $modelName : "meta-llama/llama-3.1-8b-instruct:free"); break;
            case "groq":       $model = new GroqModel($apiKey, $modelName !== "" ? $modelName : "llama-3.1-8b-instant"); break;
            case "together":   $model = new TogetherModel($apiKey, $modelName !== "" ? $modelName : "meta-llama/Llama-3.2-11B-Vision-Instruct-Turbo"); break;
            case "mistral":    $model = new MistralModel($apiKey, $modelName !== "" ? $modelName : "mistral-small-latest"); break;
            case "gemini":     $model = new GeminiModel($apiKey, $modelName !== "" ? $modelName : "gemini-2.5-flash"); break;
            case "chatgpt":    $model = new ChatGPTModel($apiKey, $modelName !== "" ? $modelName : "gpt-4o-mini"); break;
        }
        if($model !== null) {
            $this->registerModel($model);
        }
        return true;
    }

    public function hasSession($playerName) {
        return isset($this->sessions[$playerName]);
    }

    public function startSession($playerName) {
        $this->sessions[$playerName] = [];
    }

    public function endSession($playerName) {
        unset($this->sessions[$playerName]);
    }

    public function getSessionHistory($playerName) {
        return isset($this->sessions[$playerName]) ? $this->sessions[$playerName] : [];
    }

    public function appendSessionHistory($playerName, $role, $content) {
        if(isset($this->sessions[$playerName])) {
            $this->sessions[$playerName][] = ["role" => $role, "content" => $content];
        }
    }

    public function query($prompt, $modelId = null, $systemPrompt = null, callable $callback = null, $history = []) {
        if($modelId !== null) {
            $mod = $this->getModel($modelId);
        } else {
            $mod = $this->getDefaultModel();
        }
        if($mod === null) {
            throw new \exception\AIBException("No AI model available. Please configure an API key in config.yml.");
        }
        $task = new \task\AIQueryTask($mod, $prompt, $systemPrompt, $callback, $history);
        $this->getServer()->getScheduler()->scheduleAsyncTask($task);
    }

    public function querySync($prompt, $modelId = null, $systemPrompt = null) {
        if($modelId !== null) {
            $mod = $this->getModel($modelId);
        } else {
            $mod = $this->getDefaultModel();
        }
        if($mod === null) {
            throw new \exception\AIBException("No AI model available. Please configure an API key in config.yml.");
        }
        return $mod->query($prompt, $systemPrompt);
    }
}
