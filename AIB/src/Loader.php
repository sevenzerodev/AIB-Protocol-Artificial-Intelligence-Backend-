<?php
class Loader extends PluginBase
public function onEnable(){
  self::$instance = $this;
  $this->saveDefaultConfig();
  $this->config = $this->getConfig();
  $this->registeeBuiltinModels();
  $this->getServer()->getCommandMap()->register("aib", new aib\command\AIBCommand($this));
}

public static function getInstance(){
  self::$instance;
}

  public function registerBuiltinModels(){
  $mods = $this->config->get("models",[]);
  if(isset($mods["openrouter"])&&isset($mods["openrouter"]["api_key"])){
    $this->registerModel(new OpenRouterModel($mods["openrouter"]["api_key"],isset($mods["openrouter"]["model"]) ? $mods["openrouter"]["model"] : "meta-llama/llama-3.1-8b-instruct:free"));
  }
  if(isset($mods["groq"])&&isset($mods["groq"]["api_key"])){
    $this->registerModel(new GroqModel($mods["groq"]["api_key"],isset($mods["groq"]["model"]) ? $mods["groq"]["model"] : "llama-3.1-8b-instant"));
  }
  if(isset($mods["together"])&&isset($mods["together"]["api_key"])){
    $this->registerModel(new TogetherModel($mods["together"]["api_key"],isset($mods["together"]["model"]) ? $mods["together"]["model"] : "meta-llama/Llama-3.2-11B-Vision-Instruct-Turbo"));
  }
  if(isset($mods["mistral"])&&isset($mods["mistral"]["api_key"])){
    $this->registerModel(new MistralModel($mods["mistral"]["api_key"],isset($mods["mistral"]["model"]) ? $mods["mistral"]["model"] : "mistral-small-latest"));
  }
  if(isset($mods["gemini"])&&isset($mods["gemini"]["api_key"])){
    $this->registerModel(new GeminiModel($mods["gemini"]["api_key"],isset($mods["gemini"]["model"]) ? $mods["gemini"]["model"] : "gemini-2.5-flash"));
  }
  }
  public function registerModel(){
    $this->modelRegistry[$model->getModelId()] = $model;
    $this->getLogger()->info("Registered AI model: " . $model->getModelId());
  }
public function getModel($modelId){
  
}