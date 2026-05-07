<?php
namespace task;
class AIQueryTask extends pocketmine\scheduler\AsyncTask{
    private $serializedModel;
    private $modelClass;
    private $prompt;
    private $systemPrompt;
    private $result;
    private $error;

    public function __construct(\Loader $plugin, \model\AIModel $model, $prompt, $systemPrompt, $callback) {
        $this->modelClass = get_class($model);
        $this->serializedModel = serialize($model);
        $this->prompt = $prompt;
        $this->systemPrompt = $systemPrompt;
        $this->storeLocal($callback);
    }

    public function onRun(){
        $model = unserialize($this->serializedModel);
        try{
            $this->result = $model->query($this->prompt, $this->systemPrompt);
            $this->error = null;
        }catch(\Exception $e){
            $this->result = null;
            $this->error = $e->getMessage();
        }
    }

    public function onCompletion(\pocketmine\Server $server){
        $callback = $this->fetchLocal();
        if($callback !== null && is_callable($callback)){
            if($this->error !== null){
                call_user_func($callback, null, $this->error);}else{
                call_user_func($callback, $this->result, null);
            }
        }
        $event = new \event\AIResponseEvent(
            \Loader::getInstance(),
            $this->prompt,
            $this->result,
            $this->error
        );
        $event->call();
    }
}
