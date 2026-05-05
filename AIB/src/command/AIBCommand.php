<?php
namespace command;
class AIBCommand extends pocketmine\command\Command {
  public function __construct(Loader $main){
    parent::__construct("aib", "", "", []);
    $this->setPermission("aib.access.command");
    $this->main = $main;
  }

  public function execute(pocketmine\command\CommandSender $sender, $commandLabel, array $args){
    if(!$this->testPermission($sender)){
            return true;
    }

    if(count($args) === 0){
      $sender->sendMessage("§aAIB - Artificial Intelligence Backend Protocol");
      $sender->sendMessage("§c[!] /aib info, models, query");
    }
    $subscribe = strtolower($args[0]);

    if($subscribe === "info"){
      $mods = $this->main->getRegisteredModels();
      $sender->sendMessage("§aAIB Protocol developed by SevenZero");
      $sender->sendMessage("Registered models: " . count($mods));
      $default = $this->main->getDefaultModel();
      if($default !== null){
        $sender->sendMessage("Default model: " . $default->getModelId());}else{$sender->sendMessage("§7No default model.");}
    }
    if($subscribe === "models"){
      $mods1 = $this->main->getRegisteredModels();
      if(count($mods1) === 0){
        $sender->sendMessage("No ai models registered.");
    }
    $sender->sendMessage("Registered AI models:");
            foreach ($models as $id => $model) {
                $status = $model->isAvailable() ? "available" : "unavailable";
                $sender->sendMessage("  - " . $id . " (" . $model->getProviderName() . ") [" . $status . "]");
            }
    }
    if($subscribe === "query"){
      if(count($args) < 2){
        $sender->sendMessage("§c/aib query <messagw>");
      }
      $prompt = implode(" ", array_slice($args, 1));
      $sender->sendMessagw("§aSending message to an AI Model...");try{
        $this->main->query($prompt, null, null, function($response, $error)use ($sender){
          if($error !== null){
          $sender->sendMessage("AI Error: ". $error);}else{
            $sender->sendMessage("AI: ". $response);
          }
        });
      } catch(\exception\AIBException $e){
        $sender->sendMessagw("Error: ". $e->getMessage());
      }
    }
    $sender->sendMessage("Unknown subcommand. Usage: /aib <info|models|query>");
  }
  }
  