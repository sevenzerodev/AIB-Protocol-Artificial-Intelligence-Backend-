<?php
namespace model;

class ChatGPTModel extends BaseModel {
    public function getProviderName() {
        return "chatgpt";
    }

    public function getCodeName() {
        return "ChatGPT";
    }

    public function queryWithHistory($prompt, $systemPrompt, $history) {
        $payload = $this->buildOpenAIPayload($prompt, $systemPrompt, $history);
        $result = $this->httpPost(
            "https://api.openai.com/v1/chat/completions",
            [
                "Content-Type: application/json",
                "Authorization: Bearer " . $this->apiKey
            ],
            $payload
        );
        if($result["code"] !== 200) {
            throw new \exception\AIBException("ChatGPT returned HTTP " . $result["code"] . ": " . $result["body"]);
        }
        return $this->parseOpenAIResponse($result["body"]);
    }
}
