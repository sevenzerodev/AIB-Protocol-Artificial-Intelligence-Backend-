<?php
namespace model;

class MistralModel extends BaseModel {
    public function getProviderName() {
        return "mistral";
    }

    public function getCodeName() {
        return "Mistral AI";
    }

    public function queryWithHistory($prompt, $systemPrompt, $history) {
        $payload = $this->buildOpenAIPayload($prompt, $systemPrompt, $history);
        $result = $this->httpPost(
            "https://api.mistral.ai/v1/chat/completions",
            [
                "Content-Type: application/json",
                "Authorization: Bearer " . $this->apiKey
            ],
            $payload
        );
        if($result["code"] !== 200) {
            throw new \exception\AIBException("Mistral returned HTTP " . $result["code"] . ": " . $result["body"]);
        }
        return $this->parseOpenAIResponse($result["body"]);
    }
}
