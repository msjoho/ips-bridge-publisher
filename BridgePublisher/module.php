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
        // Parent is connected automatically by IPS based on the
        // parentRequirements / implemented declarations in module.json.
        // We don't call ConnectParent because that would try to *create*
        // a new MQTT Client splitter, which conflicts with the existing one.
    }

    public function ApplyChanges()
    {
        parent::ApplyChanges();
    }

    /**
     * Publish an MQTT message via the parent splitter.
     */
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
