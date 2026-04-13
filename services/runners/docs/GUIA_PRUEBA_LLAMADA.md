# 🧪 Guía de Prueba - Llamada SIP con ARI WebSocket

## ✅ Estado Actual

| Componente      | Estado                                                    |
| --------------- | --------------------------------------------------------- |
| **ARI Manager** | ✅ Running y conectado a Asterisk                         |
| **Trunk**       | ✅ `trk_default_test` configurado con ARI                 |
| **Agente**      | ✅ `agent-tito-test` listo (GPT-4o + Deepgram + Cartesia) |
| **Asterisk**    | ✅ ARI app `tito-ai` registrada                           |

---

## 📋 Configuración del Trunk

```json
{
    "trunk_id": "trk_default_test",
    "name": "Default Test Trunk",
    "mode": "inbound",
    "routes": [
        {
            "extension": "*",
            "agent_id": "agent-tito-test"
        }
    ],
    "ari_endpoint": "http://asterisk:8088",
    "app_name": "tito-ai",
    "app_password": "tito-ari-secret",
    "api_host": "apptitoai-pipecat-runners-api-1",
    "api_port": 8000
}
```

---

## 🎯 Opciones para Probar la Llamada

### Opción 1: Usar un Softphone (Recomendado)

1. **Descargar MicroSIP** (Windows) o **Zoiper** (Mac/Linux)

2. **Configurar cuenta SIP**:

    ```
    Nombre de usuario: tito
    Contraseña: tito123
    Dominio: localhost:5060 (o IP del servidor)

    O si estás en la misma máquina:
    - Server: 127.0.0.1:5060
    - Username: tito
    - Password: tito123
    ```

3. **Marcar cualquier extensión**, ej: `1000`

4. **El agente "Tito" debería responder** con un saludo

---

### Opción 2: Usar CLI de Asterisk (Desde el contenedor)

```bash
# Entrar al contenedor de Asterisk
docker exec -it apptitoai-asterisk /bin/sh

# Hacer una llamada de prueba
asterisk -rx "channel originate PJSIP/tito-inbound extension 1000@tito-inbound-ari"
```

---

### Opción 3: Usar sipp (Herramienta de testing SIP)

```bash
# Instalar sipp
apt-get install sipp

# Crear escenario simple
cat > call.xml << 'EOF'
<?xml version="1.0" encoding="ISO-8859-1" ?>
<scenario name="Basic Sipstone UAC">
  <send retrans="500">
    <![CDATA[
      INVITE sip:1000@localhost:5060 SIP/2.0
      Via: SIP/2.0/UDP [local_ip]:[local_port];branch=[branch]
      From: sipp <sip:sipp@[local_ip]:[local_port]>;tag=[pid]SIPpTag00[call_number]
      To: sut <sip:1000@localhost:5060>
      Call-ID: [call_id]
      CSeq: 1 INVITE
      Contact: sip:sipp@[local_ip]:[local_port]
      Max-Forwards: 70
      Subject: Performance Test
      Content-Type: application/sdp
      Content-Length: [len]

      v=0
      o=user1 53655765 2353687637 IN IP4 [local_ip]
      s=-
      c=IN IP4 [local_ip]
      t=0 0
      m=audio [auto_media_port] RTP/AVP 0
      a=rtpmap:0 PCMU/8000
    ]]>
  </send>
</scenario>
EOF

# Ejecutar llamada
sipp -sf call.xml localhost:5060 -m 1
```

---

## 🔍 Verificar que Funciona

### 1. Ver logs del ARI Manager:

```bash
docker logs apptitoai-pipecat-runners-api-1 -f | grep -E "(StasisStart|agent|bridge)"
```

Deberías ver:

```
[ARI trunk=trk_default_test] StasisStart: channel=..., state=Ring
[ARI trunk=trk_default_test] Inbound call: extension=1000, agent_id=agent-tito-test
[ARI trunk=trk_default_test] Created external media: ...
[ARI trunk=trk_default_test] Call setup complete: bridge=..., agent=agent-tito-test
```

### 2. Ver canales en Asterisk:

```bash
docker exec apptitoai-asterisk asterisk -rx "core show channels"
```

### 3. Verificar WebSocket conectado:

```bash
docker exec apptitoai-asterisk asterisk -rx "http show status"
```

---

## 🐛 Troubleshooting

### Error: "No agent found"

- Verificar que `agent-tito-test` existe en Redis:
    ```bash
    docker exec apptitoai-redis-1 redis-cli -a redis get agent_config:agent-tito-test
    ```

### Error: "Trunk not found"

- Verificar que el trunk tiene `mode: "inbound"`:
    ```bash
    docker exec apptitoai-redis-1 redis-cli -a redis get trunk:trk_default_test
    ```

### Error: "ARI connection failed"

- Verificar que Asterisk tiene ARI habilitado:
    ```bash
    docker exec apptitoai-asterisk asterisk -rx "ari show apps"
    ```
    Debe mostrar: `tito-ai`

### Error: "WebSocket connection refused"

- Verificar que el contenedor puede resolver el nombre del servicio:
    ```bash
    docker exec apptitoai-asterisk ping apptitoai-pipecat-runners-api-1
    ```

---

## 📊 Comandos Útiles

```bash
# Ver todos los logs
docker logs apptitoai-pipecat-runners-api-1 -f

# Ver logs solo de ARI
docker logs apptitoai-pipecat-runners-api-1 -f | grep "ARI"

# Reiniciar servicio
docker compose restart pipecat-runners-api

# Ver estado de Asterisk
docker exec apptitoai-asterisk asterisk -rx "core show uptime"
docker exec apptitoai-asterisk asterisk -rx "pjsip show endpoints"
docker exec apptitoai-asterisk asterisk -rx "ari show apps"

# Ver datos en Redis
docker exec apptitoai-redis-1 redis-cli -a redis keys "*"
```

---

## 🎉 Éxito!

Si todo funciona correctamente:

1. El softphone conecta
2. Marcas `1000`
3. Escuchas "Hola, soy Tito..."
4. Puedes conversar con el agente AI

**¡Listo para probar!** 🚀
