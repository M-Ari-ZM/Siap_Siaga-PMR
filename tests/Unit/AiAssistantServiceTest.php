<?php

namespace Tests\Unit;

use App\Models\Emergency;
use App\Services\AiAssistantService;
use Tests\TestCase;

class AiAssistantServiceTest extends TestCase
{
    public function test_fallback_knowledge_base_returns_structured_p3k_data_for_pingsan(): void
    {
        $service = new AiAssistantService();

        $emergency = new Emergency([
            'incident_type' => 'Pingsan',
            'description' => 'Siswa tiba-tiba lemas dan tidak sadarkan diri saat upacara bendera',
        ]);

        $guidance = $service->fallbackKnowledgeBase($emergency);

        $this->assertIsArray($guidance);
        $this->assertArrayHasKey('triage_level', $guidance);
        $this->assertArrayHasKey('first_aid_steps', $guidance);
        $this->assertArrayHasKey('do_nots', $guidance);
        $this->assertArrayHasKey('recommended_equipment', $guidance);
        $this->assertNotEmpty($guidance['first_aid_steps']);
        $this->assertNotEmpty($guidance['recommended_equipment']);
    }

    public function test_fallback_knowledge_base_returns_structured_p3k_data_for_luka_berdarah(): void
    {
        $service = new AiAssistantService();

        $emergency = new Emergency([
            'incident_type' => 'Luka Berdarah',
            'description' => 'Lutut lecet dan berdarah karena jatuh di lapangan basket',
        ]);

        $guidance = $service->fallbackKnowledgeBase($emergency);

        $this->assertIsArray($guidance);
        $this->assertArrayHasKey('triage_level', $guidance);
        $this->assertContains('Sarung Tangan Medis (Latex Gloves)', $guidance['recommended_equipment']);
    }

    public function test_live_gemini_api_connection(): void
    {
        $apiKey = config('services.gemini.api_key');
        if (empty($apiKey)) {
            $this->markTestSkipped('No GEMINI_API_KEY provided');
        }

        config(['services.gemini.model' => 'gemini-3.5-flash-lite']);

        $service = new \App\Services\AiAssistantService();
        $emergency = new \App\Models\Emergency([
            'incident_type' => 'Luka Berdarah',
            'description' => 'Siswa terjatuh di tangga dan siku tangan sobek berdarah deras',
        ]);

        $res = $service->generateFirstAidGuidance($emergency);
        echo "\n[DEBUG] Result Engine: " . ($res['engine_used'] ?? 'None') . "\n";
        echo "[DEBUG] Summary: " . ($res['summary'] ?? 'None') . "\n";
        echo "[DEBUG] Steps: " . json_encode($res['first_aid_steps'] ?? []) . "\n";

        $this->assertEquals('Google Gemini AI', $res['engine_used']);
    }
}
