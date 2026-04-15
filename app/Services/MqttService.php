<?php
// app/Services/MqttService.php

namespace App\Services;

use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;

class MqttService
{
    protected $client;
    protected $connected = false;
    
    public function __construct()
    {
        $this->client = new MqttClient(
            env('MQTT_HOST', '4037529a78a04d66ad4be0089551ad91.s1.eu.hivemq.cloud'),
            env('MQTT_PORT', 8884),
            'laravel_' . uniqid()
        );
    }
    
    public function publish($topic, $message, $qos = 1)
    {
        try {
            $settings = (new ConnectionSettings())
                ->setUsername(env('MQTT_USERNAME', 'lampu_control'))
                ->setPassword(env('MQTT_PASSWORD', 'Lampu_control123!'))
                ->setUseTls(true);
            
            $this->client->connect($settings);
            $this->client->publish($topic, $message, $qos);
            $this->client->disconnect();
            
            \Log::info("MQTT published: {$topic} -> {$message}");
            return true;
        } catch (\Exception $e) {
            \Log::error("MQTT publish error: " . $e->getMessage());
            return false;
        }
    }
}