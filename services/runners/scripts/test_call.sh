#!/bin/bash
# Script para hacer una llamada de prueba via Asterisk CLI

echo "🧪 Llamada de prueba SIP con ARI WebSocket"
echo "=========================================="
echo ""

# Verificar que Asterisk está corriendo
if ! docker exec apptitoai-asterisk asterisk -rx "core show version" > /dev/null 2>&1; then
    echo "❌ Error: Asterisk no está corriendo"
    exit 1
fi

echo "✅ Asterisk está corriendo"
echo ""

# Verificar que ARI está configurado
echo "📋 Verificando configuración ARI..."
APP_COUNT=$(docker exec apptitoai-asterisk asterisk -rx "ari show apps" 2>/dev/null | grep -c "tito-ai")
if [ "$APP_COUNT" -eq 0 ]; then
    echo "❌ Error: ARI app 'tito-ai' no está registrada"
    exit 1
fi
echo "✅ ARI app 'tito-ai' registrada"
echo ""

# Verificar endpoints
echo "📋 Verificando endpoints SIP..."
ENDPOINT_COUNT=$(docker exec apptitoai-asterisk asterisk -rx "pjsip show endpoints" 2>/dev/null | grep -c "tito-inbound")
if [ "$ENDPOINT_COUNT" -eq 0 ]; then
    echo "❌ Error: Endpoint 'tito-inbound' no configurado"
    exit 1
fi
echo "✅ Endpoint 'tito-inbound' configurado"
echo ""

# Verificar trunk en Redis
echo "📋 Verificando trunk en Redis..."
TRUNK_EXISTS=$(docker exec apptitoai-redis-1 redis-cli -a redis exists trunk:trk_default_test 2>/dev/null)
if [ "$TRUNK_EXISTS" -eq 0 ]; then
    echo "❌ Error: Trunk 'trk_default_test' no encontrado en Redis"
    exit 1
fi
echo "✅ Trunk 'trk_default_test' encontrado"
echo ""

# Verificar agente en Redis
echo "📋 Verificando agente en Redis..."
AGENT_EXISTS=$(docker exec apptitoai-redis-1 redis-cli -a redis exists agent_config:agent-tito-test 2>/dev/null)
if [ "$AGENT_EXISTS" -eq 0 ]; then
    echo "❌ Error: Agente 'agent-tito-test' no encontrado en Redis"
    exit 1
fi
echo "✅ Agente 'agent-tito-test' encontrado"
echo ""

# Mostrar configuración
echo "📊 Configuración del trunk:"
docker exec apptitoai-redis-1 redis-cli -a redis get trunk:trk_default_test 2>/dev/null | python3 -m json.tool 2>/dev/null | grep -E "(trunk_id|agent_id|ari_endpoint|mode)"
echo ""

echo "📊 Agente configurado:"
docker exec apptitoai-redis-1 redis-cli -a redis get agent_config:agent-tito-test 2>/dev/null | python3 -m json.tool 2>/dev/null | grep -E "(agent_id|name|llm|tts)"
echo ""

# Iniciar llamada de prueba
echo "🚀 Iniciando llamada de prueba..."
echo "================================"
echo ""
echo "Comando a ejecutar:"
echo "  channel originate PJSIP/tito-inbound/extension 1000@tito-inbound-ari"
echo ""

# Ejecutar llamada
RESULT=$(docker exec apptitoai-asterisk asterisk -rx "channel originate PJSIP/tito-inbound/extension 1000@tito-inbound-ari" 2>&1)

if echo "$RESULT" | grep -q "failed\|error\|Error"; then
    echo "❌ Error al iniciar llamada:"
    echo "$RESULT"
    exit 1
fi

echo "✅ Llamada iniciada!"
echo ""
echo "📋 Resultado:"
echo "$RESULT"
echo ""

# Ver canales activos
echo "📊 Canales activos en Asterisk:"
docker exec apptitoai-asterisk asterisk -rx "core show channels" 2>/dev/null | head -20
echo ""

# Instrucciones para ver logs
echo "🔍 Para ver los logs de la llamada, ejecuta en otra terminal:"
echo "   docker logs apptitoai-pipecat-runners-api-1 -f | grep -E 'ARI|agent|bridge'"
echo ""
echo "🎉 Si todo funciona correctamente, deberías ver:"
echo "   - 'StasisStart' event"
echo "   - 'Created external media'"
echo "   - 'Call setup complete'"
echo ""
