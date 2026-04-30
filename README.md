# BridgePublisher

A minimal custom [IP-Symcon](https://www.symcon.de/) module that exposes a
clean PHP function for publishing MQTT messages via the **native Symcon
MQTT Client splitter**.

## Why this exists

IP-Symcon's native MQTT Client splitter
(`{F7A0DD2E-7684-95C0-64C2-D2A9DC47577B}`) does not expose a public
`Publish` function that scripts can call. Out of the box you can only
*receive* messages or have other modules push to it via internal
splitter data packets.

Common workarounds:

1. Install a third-party `MQTTPublish` module — adds an external
   dependency for one tiny piece of functionality.
2. Construct the splitter packet by hand from every script — duplicated
   boilerplate, easy to get wrong.

`BridgePublisher` is the smallest possible native solution: a thin
module that sits under the native MQTT Client splitter and forwards
messages to it via `SendDataToParent()`. No third-party dependencies,
no protocol re-implementation, just a clean function call.

## Installation

In IP-Symcon:

1. Open **Module Control** (under *Core Instances*).
2. Click **Add** and paste the repo URL:

   ```
   https://github.com/msjoho/ips-bridge-publisher
   ```

3. Once the module is installed, create a new instance:
   - **Object Tree → Add Instance → Bridge Publisher**
   - When prompted for a parent, select your existing **MQTT Client**
     splitter (or create one first if you don't have one).

That's it. The instance ID of the `Bridge Publisher` you just created
is what you'll pass to the publish function.

## Usage

From any IP-Symcon script:

```php
// $instanceId is the ID of your Bridge Publisher instance
BRIDGEPUB_Publish($instanceId, 'home/livingroom/light', 'on');

// With QoS 1 and retain flag:
BRIDGEPUB_Publish($instanceId, 'home/status', 'online', 1, true);
```

### Function signature

```php
BRIDGEPUB_Publish(
    int    $instanceId,
    string $topic,
    string $payload,
    int    $qos    = 0,      // 0, 1, or 2
    bool   $retain = false
): bool
```

Returns `true` on success, `false` if the parent splitter rejected the
packet.

### Example: publish JSON state on a variable change

```php
<?php
// Triggered when a tracked variable changes
$payload = json_encode([
    'value'     => GetValue($_IPS['VARIABLE']),
    'timestamp' => time(),
]);

BRIDGEPUB_Publish(54321, 'home/sensors/temperature', $payload, 0, true);
```

## Compatibility

- Requires **IP-Symcon 7.0** or later.
- The instance **must** be parented to the native Symcon MQTT Client
  splitter (`{F7A0DD2E-7684-95C0-64C2-D2A9DC47577B}`). The module
  enforces this via `parentRequirements` in `module.json`, so IPS will
  block configurations that don't satisfy it.

## How it works

The module's `Publish()` function builds the splitter data packet that
the native MQTT Client expects:

| Field              | Value                                       |
|--------------------|---------------------------------------------|
| `DataID`           | `{7F7632D9-FA40-4F38-8DEA-C83CD4325A32}` (MQTT TX) |
| `PacketType`       | `3` (PUBLISH)                               |
| `QualityOfService` | `0` / `1` / `2`                             |
| `Retain`           | `bool`                                      |
| `Topic`            | the MQTT topic                              |
| `Payload`          | the message body                            |

It then calls `SendDataToParent(json_encode($packet))`, which is the
documented module-to-parent communication primitive. The parent
splitter handles the actual MQTT protocol work.

## License

[MIT](LICENSE)
