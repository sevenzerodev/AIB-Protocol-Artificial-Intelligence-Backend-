<?php
namespace model;

class GeminiModel extends BaseModel {
    public function getProviderName() {
        return "gemini";
    }

    public function getCodeName() {
        return "Google Gemini";
    }

    public function queryWithHistory($prompt, $systemPrompt, $history) {
        $contents = [];
        if($systemPrompt !== null && $systemPrompt !== "") {
            $contents[] = ["role" => "user", "parts" => [["text" => $systemPrompt]]];
            $contents[] = ["role" => "model", "parts" => [["text" => "Understood."]]];
        }
        foreach($history as $entry) {
            $role = $entry["role"] === "assistant" ? "model" : "user";
            $contents[] = ["role" => $role, "parts" => [["text" => $entry["content"]]]];
        }
        $contents[] = ["role" => "user", "parts" => [["text" => $prompt]]];

        $payload = json_encode([
            "contents" => $contents,
            "generationConfig" => [
                "temperature" => 0.7,
                "maxOutputTokens" => 2048,
                "responseMimeType" => "text/plain"
            ]
        ]);

        $url = "https://generativelanguage.googleapis.com/v1beta/models/" . urlencode($this->modelName) . ":generateContent";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "x-goog-api-key: " . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);

        $resp    = curl_exec($ch);
        $err     = curl_error($ch);
        $code    = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $hdrSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        if($err !== "") {
            throw new \exception\AIBException("Gemini cURL error: " . $err);
        }

        $body = substr($resp, $hdrSize);

        if($code !== 200) {
            throw new \exception\AIBException("Gemini returned HTTP " . $code . ": " . $body);
        }

        $decoded = json_decode($body, true);
        if($decoded === null) {
            throw new \exception\AIBException("Failed to decode Gemini response.");
        }
        if(isset($decoded["error"])) {
            throw new \exception\AIBException("Gemini API error: " . $decoded["error"]["message"]);
        }
        if(!isset($decoded["candidates"][0]["content"]["parts"][0]["text"])) {
            throw new \exception\AIBException("Unexpected Gemini response format: " . $body);
        }
        return $decoded["candidates"][0]["content"]["parts"][0]["text"];
    }
}
