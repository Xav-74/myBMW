import ssl
import json
import asyncio
import paho.mqtt.client as mqtt

from jeedomdaemon import BaseDaemon, BaseConfig


class DaemonConfig(BaseConfig):
    
    def __init__(self):
        super().__init__()

        self.add_argument("--host", help="MQTT host", type=str)
        self.add_argument("--port", help="MQTT port", type=int)
        self.add_argument("--username", help="MQTT username", type=str)
        self.add_argument("--password", help="MQTT password (ID token)", type=str)
        
    @property
    def mqtt_host(self): return str(self._args.host)
    @property
    def mqtt_port(self): return int(self._args.port)
    @property
    def mqtt_username(self): return str(self._args.username)
    @property
    def mqtt_password(self): return str(self._args.password)
    

class MyBMWDaemon(BaseDaemon):
    def __init__(self) -> None:
        self._config = DaemonConfig()
        super().__init__(self._config, self.on_start, self.on_message, self.on_stop)
        self._mqtt_client = None
        self._subscriptions = set()
        self._connected = False
        
       
    async def on_start(self):
        self._logger.info("Starting myBMW daemon...")
        self._init_mqtt()
        self._logger.info("Connecting to BMW MQTT Broker...")
        try:
            self._mqtt_client.connect(self._config.mqtt_host, self._config.mqtt_port, keepalive=30)
            self._mqtt_client.loop_start()
        except Exception as e:
            self._logger.error(f"Failed to connect to MQTT broker: {e}")


    async def on_message(self, message: dict):
        try:
            action = message['action']
            param = message['param']
                                   
            if action == 'refreshToken':
                if param:
                    self._logger.info("Received new token from Jeedom")
                    self._config._args.password = param
                    self._mqtt_client.username_pw_set(self._config.mqtt_username, param)
                    await asyncio.sleep(2)
                    try:
                        self._logger.info("Reconnecting to MQTT broker with new token...")
                        self._mqtt_client.reconnect()
                    except Exception as e:
                        self._logger.error(f"Failed to connect to MQTT broker after token refresh: {e}")
                else:
                    self._logger.warning("Received no token data")
                return            
            
            elif action == 'subscribe':
                topic = f"{self._config.mqtt_username}/{param}"
                if topic not in self._subscriptions:
                    self._subscriptions.add(topic)
                    if self._connected:
                        self._mqtt_client.subscribe(topic)
                        self._logger.info(f"Subscribed to topic {topic}")
                    else:
                        self._logger.warning(f"Will subscribe to topic {topic} after reconnect")

            elif  action == 'unsubscribe':
                topic = f"{self._config.mqtt_username}/{param}"
                if topic in self._subscriptions:
                    self._subscriptions.remove(topic)
                    if self._connected:
                        self._mqtt_client.unsubscribe(topic)
                    self._logger.info(f"Unsubscribed from topic {topic}")
        
        except Exception as e:
            self._logger.error(f"Error handling message from Jeedom: {e}")


    async def on_stop(self):
        self._logger.info("Stopping myBMW daemon...")
        if self._mqtt_client:
            self._mqtt_client.loop_stop()
            self._mqtt_client.disconnect()
        self._subscriptions.clear()
        self._logger.info("Daemon stopped")


    def _init_mqtt(self):
        # MQTT client initialization
        self._mqtt_client = mqtt.Client(mqtt.CallbackAPIVersion.VERSION2)
        self._mqtt_client.username_pw_set(self._config.mqtt_username, self._config.mqtt_password)
        self._mqtt_client.on_connect = self._on_connect
        self._mqtt_client.on_message = self._on_message
        self._mqtt_client.on_disconnect = self._on_disconnect
        self._mqtt_client.on_subscribe = self._on_subscribe
        self._mqtt_client.on_unsubscribe = self._on_unsubscribe
        self._mqtt_client.reconnect_delay_set(min_delay=10, max_delay=300)
        # BMW TLS configuration
        tls_context = ssl.create_default_context(ssl.Purpose.SERVER_AUTH)
        tls_context.check_hostname = True
        tls_context.verify_mode = ssl.CERT_REQUIRED
        tls_context.minimum_version = ssl.TLSVersion.TLSv1_2
        self._mqtt_client.tls_set_context(tls_context)
        self._mqtt_client.tls_insecure_set(False)
        
        
    def _on_connect(self, client, userdata, flags, reasonCode, properties=None, *args):
        if reasonCode == mqtt.CONNACK_ACCEPTED:
            self._connected = True
            self._logger.info(f"Connected successfully to BMW MQTT broker {self._config.mqtt_host}:{self._config.mqtt_port}")
            for topic in self._subscriptions:
                client.subscribe(topic)
                self._logger.info(f"Re-subscribed to {topic}")
        else:
            self._connected = False
            self._logger.warning(f"MQTT connection failed with code {reasonCode}")

            if "bad user name or password" in str(reasonCode).lower():
                self._logger.warning("Authentication failed — Refresh token required")
                asyncio.run_coroutine_threadsafe(
                    self.send_to_jeedom({"event": "refresh_token_required", "reason": str(reasonCode)}),
                    self._loop
                )

    def _on_disconnect(self, client, userdata, reasonCode, properties=None, *args):
        self._connected = False
        self._logger.warning(f"Disconnected from MQTT broker (code {reasonCode})")
        

    def _on_message(self, client, userdata, msg):
        try:
            payload = json.loads(msg.payload.decode("utf-8"))
            asyncio.run_coroutine_threadsafe(
                self.send_to_jeedom({"topic": msg.topic, "data": payload}),
                self._loop
            )
            self._logger.debug(f"Message received on {msg.topic} : {payload}")
        except Exception as e:
            self._logger.error(f"Error processing MQTT message: {e}")


    def _on_subscribe(self, client, userdata, mid, granted_qos, properties=None):
        self._logger.info(f"Subscription acknowledged (mid={mid}, {granted_qos})")


    def _on_unsubscribe(self, client, userdata, mid, properties=None, reasonCodes=None):
        self._logger.info(f"Unsubscription acknowledged (mid={mid})")


MyBMWDaemon().run()