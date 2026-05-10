<?php
namespace event;

class ChatListener implements \pocketmine\event\Listener {
    private $main;

    public function __construct(\Loader $main) {
        $this->main = $main;
    }

    public function onChat(\pocketmine\event\player\PlayerChatEvent $event) {
        $player = $event->getPlayer();
        $name = $player->getName();
        if(!$this->main->hasSession($name)) {
            return;
        }
        $event->setCancelled(true);
        $msg = $event->getMessage();
        if($msg === "*#stop-") {
            $this->main->endSession($name);
            $player->sendMessage("§aAIB §7>> §fAI chat session ended.");
            return;
        }
        $player->sendMessage("§7[You] §f" . $msg);
        $history = $this->main->getSessionHistory($name);
        $this->main->appendSessionHistory($name, "user", $msg);
        try {
            $this->main->query($msg, null, null, function($response, $error) use ($player, $name) {
                if($error !== null) {
                    $player->sendMessage("§cAI Error: " . $error);
                    return;
                }
                $player->sendMessage("§aAI §7>> §f" . $response);
                $this->main->appendSessionHistory($name, "assistant", $response);
            }, $history);
        } catch(\exception\AIBException $e) {
            $player->sendMessage("§cError: " . $e->getMessage());
        }
    }
}
