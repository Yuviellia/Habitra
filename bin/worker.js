const amqp = require('amqplib');
const RETRY_INTERVAL = 5000;

async function connectWithRetry() {
    while (true) {
        try {
            const conn = await amqp.connect('amqp://rabbitmq');
            console.log("[✔] Connected to RabbitMQ");
            return conn;
        } catch (err) {
            console.error("[!] RabbitMQ not ready. Retrying in 5 seconds...");
            await new Promise(resolve => setTimeout(resolve, RETRY_INTERVAL));
        }
    }
}

async function work() {
    const conn = await connectWithRetry();
    const channel = await conn.createChannel();
    const queue = 'habit_queue';

    await channel.assertQueue(queue, { durable: true });
    await channel.prefetch(1, false);

    console.log(`[x] Waiting for messages in ${queue}. To exit press CTRL+C`);

    await channel.consume(queue, msg => {
        const content = msg.content.toString();
        console.log(`[.] Received: '${content}'`);

        // tutaj cos sie wbije
        const secs = content.split('.').length - 1;
        setTimeout(() => {
            console.log(`[v] Done processing: '${content}'`);
            channel.ack(msg);
        }, secs * 1000);
    }, { noAck: false });
}

work().catch(err => {
    console.error("[FATAL] Worker crashed:", err);
    process.exit(1);
});
