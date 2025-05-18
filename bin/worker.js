const amqp = require('amqplib');

async function work() {
    const conn = await amqp.connect('amqp://rabbitmq');
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

    }, {noAck: false});
}

work().catch(console.error);
