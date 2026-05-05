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
      $moda = $this->main->getRegisteredModels
    }