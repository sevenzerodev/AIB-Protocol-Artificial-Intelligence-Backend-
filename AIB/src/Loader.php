<?php
class Loader extends PluginBase
public function onEnable(){
  self::$instance = $this;
  $this->saveDefaultConfig();
  $this->config = $this->getConfig();
  $this->registeeBuiltinModels();
  $this->getServer()->getCommandMap()->register("aib", new aib\command\AIBCommand($this));
}
