#!/bin/bash
# Script para configurar trunk de prueba con ARI

REDIS_PASS="redis"
TRUNK_ID="trk_default_test"

echo "Actualizando trunk $TRUNK_ID con configuración ARI..."

# Obtener trunk actual
TRUNK_DATA=$(docker exec apptitoai-redis-1 redis-cli -a $REDIS_PASS get trunk:$TRUNK_ID)

echo "Trunk actual:"
echo $TRUNK_DATA | python3 -m json.tool 2>/dev/null || echo $TRUNK_DATA

# Actualizar trunk con campos ARI
docker exec apptitoai-redis-1 redis-cli -a $REDIS_PASS set trunk:$TRUNK_ID '{
  "trunk_id": "trk_default_test",
  "name": "Default Test Trunk",
  "tenant_id": "tenant-test",
  "workspace_slug": "default",
  "mode": "inbound",
  "max_concurrent_calls": 10,
  "codecs": ["ulaw", "alaw"],
  "status": "active",
  "inbound_auth": {
    "auth_type": "ip",
    "allowed_ips": ["0.0.0.0/0"]
  },
  "routes": [
    {
      "extension": "*",
      "agent_id": "agent-tito-test",
      "priority": 0,
      "enabled": true
    }
  ],
  "sip_host": "default.sip.tito.ai",
  "sip_port": 5060,
  "ari_endpoint": "http://asterisk:8088",
  "app_name": "tito-ai",
  "app_password": "tito-ari-secret",
  "api_host": "apptitoai-pipecat-runners-api-1",
  "api_port": 8000,
  "created_at": 1712900000,
  "updated_at": 1712900000
}'

echo ""
echo "✅ Trunk actualizado con configuración ARI"
echo ""
echo "Verificando..."
docker exec apptitoai-redis-1 redis-cli -a $REDIS_PASS get trunk:$TRUNK_ID | python3 -m json.tool 2>/dev/null || echo "Done"
