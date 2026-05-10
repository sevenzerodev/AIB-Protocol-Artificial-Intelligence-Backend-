<?php
namespace model;

class TogetherModel extends BaseModel {
    public function getProviderName() {
        return "together";
    }

    public function getCodeName() {
        return "Together AI";
    }

    public function queryWithHistory($prompt, $systemPrompt, $history) {
        $payload = $this->buildOpenAIPayload($prompt, $systemPrompt, $history);
        $result = $this->httpPost(
            "https://api.together.xyz/v1/chat/completions",
            [
                "Content-Type: application/json",
                "Authorization: Bearer " . $this->apiKey
            ],
            $payload
        );
        if($result["code"] !== 200) {
            throw new \exception\AIBException("Together AI returned HTTP " . $result["code"] . ": " . $result["body"]);
        }
        return $this->parseOpenAIResponse($result["body"]);
    }
}
