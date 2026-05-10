<?php
namespace model;

class GroqModel extends BaseModel {
    public function getProviderName() {
        return "groq";
    }

    public function getCodeName() {
        return "Groq";
    }

    public function queryWithHistory($prompt, $systemPrompt, $history) {
        $payload = $this->buildOpenAIPayload($prompt, $systemPrompt, $history);
        $result = $this->httpPost(
            "https://api.groq.com/openai/v1/chat/completions",
            [
                "Content-Type: application/json",
                "Authorization: Bearer " . $this->apiKey
            ],
            $payload
        );
        if($result["code"] !== 200) {
            throw new \exception\AIBException("Groq returned HTTP " . $result["code"] . ": " . $result["body"]);
        }
        return $this->parseOpenAIResponse($result["body"]);
    }
}
