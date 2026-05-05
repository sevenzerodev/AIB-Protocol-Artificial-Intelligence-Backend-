<?php
class Loader extends PluginBase
public function onEnable(){
  $this->getServer()->getPluginManager()->registerEvents($this,$this);
}