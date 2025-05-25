import React, { useState, useEffect } from 'react';

export default function ModeSwitchButton() {
    const [darkMode, setDarkMode] = useState(false);

    useEffect(() => {
        const saved = localStorage.getItem('darkMode');
        if (saved === 'true') {
            document.documentElement.classList.add('dark-mode');
            setDarkMode(true);
        }
    }, []);

    const toggleMode = () => {
        if (darkMode) {
            document.documentElement.classList.remove('dark-mode');
            localStorage.setItem('darkMode', 'false');
            setDarkMode(false);
        } else {
            document.documentElement.classList.add('dark-mode');
            localStorage.setItem('darkMode', 'true');
            setDarkMode(true);
        }
    };

    return (
        <button
            onClick={toggleMode}
            aria-label="Switch color mode"
            className="mode-switch-button"
        >
            {darkMode ? (
                <i className="fa-regular fa-sun"></i>
            ) : (
                <i className="fa-regular fa-moon"></i>
            )}
        </button>
    );
}
