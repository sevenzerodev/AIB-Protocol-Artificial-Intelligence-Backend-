<?php
namespace task;

class AIQueryTask extends \pocketmine\scheduler\AsyncTask {
    public $serializedModel;
    public $prompt;
    public $systemPrompt;
    public $callbackId;
    public $serializedHistory;

    public function __construct(\model\AIModel $model, $prompt, $systemPrompt, $callback, $history = []) {
        static $counter = 0;
        $counter++;
        $this->callbackId = $counter;
        $this->serializedModel = serialize($model);
        $this->prompt = $prompt;
        $this->systemPrompt = $systemPrompt;
        $this->serializedHistory = serialize($history);
        \Loader::getInstance()->storeCallback($this->callbackId, $callback);
    }

    public function onRun() {
        $model = unserialize($this->serializedModel);
        $history = unserialize($this->serializedHistory);
        try {
            $result = $model->queryWithHistory($this->prompt, $this->systemPrompt, $history);
            $this->setResult(["result" => $result, "error" => null, "callbackId" => $this->callbackId, "prompt" => $this->prompt], true);
        } catch(\Exception $e) {
            $this->setResult(["result" => null, "error" => $e->getMessage(), "callbackId" => $this->callbackId, "prompt" => $this->prompt], true);
        }
    }

    public function onCompletion(\pocketmine\Server $server) {
        $data = $this->getResult();
        $callback = \Loader::getInstance()->fetchCallback($data["callbackId"]);
        if($callback !== null && is_callable($callback)) {
            call_user_func($callback, $data["result"], $data["error"]);
        }
        $event = new \event\AIResponseEvent(
            \Loader::getInstance(),
            $data["prompt"],
            $data["result"],
            $data["error"]
        );
        $server->getPluginManager()->callEvent($event);
    }
}
