import React, { useEffect, useState } from 'react';

export default function Clock() {
    const [time, setTime] = useState({
        hours: '--',
        minutes: '--',
        period: '--',
    });

    useEffect(() => {
        const updateClock = () => {
            const now = new Date();
            let hours = now.getHours();
            const period = hours >= 12 ? ' PM' : ' AM';
            hours = hours % 12 || 12;
            const minutes = now.getMinutes().toString().padStart(2, '0');
            setTime({ hours, minutes, period });
        };

        updateClock();
        const interval = setInterval(updateClock, 1000);
        return () => clearInterval(interval);
    }, []);

    return (
        <div className="clock">
            <span id="hours">{time.hours}</span>
            :
            <span id="minutes">{time.minutes}</span>
            <span id="period">{time.period}</span>
        </div>
    );
}
