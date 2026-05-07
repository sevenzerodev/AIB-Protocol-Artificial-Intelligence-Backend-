<?php
namespace model;
class MistralModel extends BaseModel{

    public function getProviderName(){
        return "mistral";
    }

    public function query($prompt, $systemPrompt = null){
        $payload = $this->buildOpenAIPayload($prompt, $systemPrompt);
        $result = $this->httpPost(
            "https://api.mistral.ai/v1/chat/completions",
            [
                "Content-Type: application/json",
                "Authorization: Bearer " . $this->apiKey
            ],
            $payload
        );
        if($result["code"] !== 200){
            throw new \aib\exception\AIBException("Mistral returned HTTP " . $result["code"] . ": " . $result["body"]);
        }
        return $this->parseOpenAIResponse($result["body"]);
    }
}
