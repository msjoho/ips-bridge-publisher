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

        // Connect under the native MQTT Client splitter
        $this->ConnectParent('{F7A0DD2E-7684-95C0-64C2-D2A9DC47577B}');
    }

    public function ApplyChanges()
    {
        parent::ApplyChanges();
    }

    /**
     * Publish an MQTT message via the parent splitter.
     *
     * @param string $topic   MQTT topic.
     * @param string $payload Message body.
     * @param int    $qos     0, 1, or 2.
     * @param bool   $retain  Whether broker should retain the message.
     * @return bool True on success.
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
