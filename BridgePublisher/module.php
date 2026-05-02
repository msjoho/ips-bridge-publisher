<?php

/**
 * BridgePublisher — minimal IPS module that publishes MQTT messages
 * via the native MQTT Client splitter, exposing a clean public function.
 *
 * Usage from any IPS script:
 *   BRIDGEPUB_Publish($instanceId, $topic, $payload, $qos = 0, $retain = false);
 */
class BridgePublisher extends IPSModule
{
    public function Create()
    {
        parent::Create();

        // IPS 8.x appears to require at least one registered property
        // for the data-flow slot to initialize correctly. We don't
        // actually use this property — it's purely a hook so IPS sees
        // the module as fully configured.
        $this->RegisterPropertyString('Note', 'Internal MQTT publish helper');
    }

    public function ApplyChanges()
    {
        parent::ApplyChanges();
    }

    public function Publish(string $topic, string $payload, int $qos = 0, bool $retain = false): bool
    {
        $packet = [
            'DataID'           => '{7F7632D9-FA40-4F38-8DEA-C83CD4325A32}',
            'PacketType'       => 3,
            'QualityOfService' => $qos,
            'Retain'           => $retain,
            'Topic'            => $topic,
            'Payload'          => $payload,
        ];

        $result = $this->SendDataToParent(json_encode($packet));
        return $result !== false;
    }
}
