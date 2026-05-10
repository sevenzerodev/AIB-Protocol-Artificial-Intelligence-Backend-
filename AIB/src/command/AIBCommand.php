<?php
namespace command;

class AIBCommand extends \pocketmine\command\Command {
    private $main;

    public function __construct(\Loader $main) {
        parent::__construct("aib", "Commands", "/aib <info, model, models, unknown, query, mchange, aikey>", []);
        $this->setPermission("aib.access.command");
        $this->main = $main;
    }

    public function execute(\pocketmine\command\CommandSender $sender, $commandLabel, array $args) {
        if(!$this->testPermission($sender)) {
            return true;
        }
        if(count($args) === 0) {
            $sender->sendMessage("§aAIB - Artificial Intelligence Backend Protocol");
            $sender->sendMessage("§cUse /aib <info|mode, |models, unknown, query, mchange, aikey>");
            return true;
        }
        $sub = strtolower($args[0]);
        if($sub === "info") {
            $mods = $this->main->getRegisteredModels();
            $sender->sendMessage("§aAIB developed by SevenZero");
            $sender->sendMessage("Registered models: " . count($mods));
            $default = $this->main->getDefaultModel();
            if($default !== null) {
                $sender->sendMessage("Default model: " . $default->getCodeName() . " (" . $default->getModelId() . ")");
            } else {
                $sender->sendMessage("§7No default model.");
            }
            return true;
        }
        if($sub === "model") {
            $active = $this->main->getDefaultModel();
            if($active === null) {
                $sender->sendMessage("§cNo active AI model.");
                return true;
            }
            $sender->sendMessage("Active model: " . $active->getCodeName() . " (" . $active->getModelId() . ")");
            return true;
        }
        if($sub === "models") {
            $mods = $this->main->getRegisteredModels();
            if(count($mods) === 0) {
                $sender->sendMessage("No AI models registered.");
                return true;
            }
            $sender->sendMessage("Registered AI models:");
            foreach($mods as $id => $model) {
                $status = $model->isAvailable() ? "§aavailable§f" : "§cunavailable§f";
                $sender->sendMessage("  - " . $model->getCodeName() . " [" . $id . "] [" . $status . "]");
            }
            return true;
        }
        if($sub === "unknown") {
            $mods = $this->main->getRegisteredModels();
            $allProviders = ["openrouter" => "OpenRouter", "groq" => "Groq", "together" => "Together AI", "mistral" => "Mistral AI", "gemini" => "Google Gemini", "chatgpt" => "ChatGPT"];
            $noKey = [];
            foreach($allProviders as $provider => $codeName) {
                $found = false;
                foreach($mods as $id => $model) {
                    if($model->getProviderName() === $provider) {
                        $found = true;
                        break;
                    }
                }
                if(!$found) {
                    $noKey[] = $codeName . " (" . $provider . ")";
                }
            }
            if(count($noKey) === 0) {
                $sender->sendMessage("All models have API keys configured.");
                return true;
            }
            $sender->sendMessage("Models without API keys:");
            foreach($noKey as $entry) {
                $sender->sendMessage("  - " . $entry);
            }
            return true;
        }
        if($sub === "mchange") {
            if(count($args) < 2) {
                $sender->sendMessage("§cUsage: /aib mchange <modelId>");
                $sender->sendMessage("§7Use /aib models to see available model IDs.");
                return true;
            }
            $modelId = $args[1];
            $model = $this->main->getModel($modelId);
            if($model === null) {
                $sender->sendMessage("§cModel not found: " . $modelId);
                $sender->sendMessage("§7Use /aib models to see usable model IDs.");
                return true;
            }
            $this->main->setActiveModel($modelId);
            $sender->sendMessage("Active model changed to: " . $model->getCodeName() . " (" . $modelId . ")");
            return true;
        }
        if($sub === "aikey") {
            if(count($args) < 3) {
                $sender->sendMessage("§cUsage: /aib aikey <provider> <key>");
                $sender->sendMessage("§7Models: openrouter, groq, together, mistral, gemini, chatgpt");
                return true;
            }
            $provider = strtolower($args[1]);
            $key = $args[2];
            $result = $this->main->setModelKey($provider, $key);
            if(!$result) {
                $sender->sendMessage("§cUnknown provider: " . $provider);
                $sender->sendMessage("§7Models: openrouter, groq, together, mistral, gemini, chatgpt");
                return true;
            }
            $mods = $this->main->getRegisteredModels();
            $codeName = $provider;
            foreach($mods as $id => $model) {
                if($model->getProviderName() === $provider) {
                    $codeName = $model->getCodeName();
                    break;
                }
            }
            $sender->sendMessage("API key updated for: " . $codeName);
            return true;
        }
        if($sub === "query") {
            $name = $sender->getName();
            if($this->main->hasSession($name)) {
                $this->main->endSession($name);
                $sender->sendMessage("§aAIB §7>> §fAI chat session ended.");
                return true;
            }
            $active = $this->main->getDefaultModel();
            $modelName = $active !== null ? $active->getCodeName() : "Unknown";
            $this->main->startSession($name);
            $sender->sendMessage("§aAIB §7>> §fAI chat session started with §a" . $modelName . "§f.");
            $sender->sendMessage("§aAIB §7>> §fSend §e*#stop-§f to end the session.");
            return true;
        }
        $sender->sendMessage("§cUnknown subcommand. Usage: /aib <info|model|models|unknown|query|mchange|aikey>");
        return true;
    }
}
