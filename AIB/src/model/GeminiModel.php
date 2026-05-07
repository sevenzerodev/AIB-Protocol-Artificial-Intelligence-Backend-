<?php
namespace model;

class GeminiModel extends BaseModel{

    public function getProviderName(){
        return "gemini";
    }
    public function query($prompt, $systemPrompt = null){
        $contents = [];
        if($systemPrompt !== null && $systemPrompt !== ""){
            $contents[] = [
                "role" => "user",
                "parts" => [["text" => $systemPrompt]]
            ];
            $contents[] = [
                "role" => "model",
                "parts" => [["text" => "Understood."]]
            ];
        }

        $contents[] = [
            "role" => "user",
            "parts" => [["text" => $prompt]]
        ];
        $payload = json_encode(["contents" => $contents]);
        $url = "https://generativelanguage.googleapis.com/v1beta/models/" . urlencode($this->modelName) . ":generateContent?key=" . urlencode($this->apiKey);

        $result = $this->httpPost(
            $url,
            ["Content-Type: application/json"],
            $payload
        );

        if($result["code"] !== 200){
            throw new \exception\AIBException("Gemini returned HTTP " . $result["code"] . ": " . $result["body"]);
        }

        $decoded = json_decode($result["body"], true);
        if ($decoded === null){
            throw new \exception\AIBException("Failed to decode Gemini response.");
        }
        if(isset($decoded["error"])){
            throw new \exception\AIBException("Gemini API error: " . $decoded["error"]["message"]);
        }
        if(!isset($decoded["candidates"][0]["content"]["parts"][0]["text"])){
            throw new \aib\exception\AIBException("Unexpected Gemini response format.");
        }
        return $decoded["candidates"][0]["content"]["parts"][0]["text"];
    }
}
