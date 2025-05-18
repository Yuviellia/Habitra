import React, { useEffect, useState } from 'react';
import Clock from '../components/Clock';
import '../css/variables.css';
import '../css/main.css';

function Habits() {
    const [tags, setTags] = useState([]);
    const [newTag, setNewTag] = useState('');
    const [refreshing, setRefreshing] = useState(false);
    const [submitting, setSubmitting] = useState(false);
    const [deleting, setDeleting] = useState({});
    const [marking, setMarking] = useState({});
    const [clock, setClock] = useState({ hours: '--', minutes: '--', period: '--' });
    const [weekOffset, setWeekOffset] = useState(0);

    const getAuthToken = () => localStorage.getItem('token');

    const fetchHabits = () => {
        setRefreshing(true);
        const token = getAuthToken();
        fetch('http://127.0.0.1:8000/api/habits', {
            headers: {
                Authorization: `Bearer ${token}`,
                'Content-Type': 'application/json',
            },
        })
            .then((res) => {
                if (res.status === 404) {
                    setTags([]);
                    return null;
                }
                if (!res.ok) {
                    throw new Error(`Unexpected status ${res.status}`);
                }
                return res.json();
            })
            .then((data) => {
                if (data) {
                    setTags(Array.isArray(data) ? data : []);
                }
            })
            .catch((err) => {
                console.error(err);
                setTags([]);
            })
            .finally(() => {
                setRefreshing(false);
            });
    };

    useEffect(() => {
        fetchHabits();
    }, []);

    const refreshHabits = () => fetchHabits();

    const handleNewTag = (e) => {
        e.preventDefault();
        setSubmitting(true);
        const token = getAuthToken();
        fetch('http://127.0.0.1:8000/api/habits', {
            method: 'POST',
            headers: {
                Authorization: `Bearer ${token}`,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ name: newTag }),
        })
            .then((res) => res.json())
            .then((data) => {
                setTags((prev) => [...prev, { ...data.habit, marked_dates: [] }]);
                setNewTag('');
            })
            .finally(() => setSubmitting(false));
    };

    const handleMarkDate = (habitId, date) => {
        setMarking((prev) => ({ ...prev, [habitId]: true }));
        const token = getAuthToken();
        fetch(`http://127.0.0.1:8000/api/habits/${habitId}/mark`, {
            method: 'POST',
            headers: {
                Authorization: `Bearer ${token}`,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ date }),
        })
            .then((res) => res.json())
            .then((data) => {
                setTags((prev) =>
                    prev.map((tag) => {
                        if (tag.id !== habitId) return tag;
                        if (data.marked) {
                            return {
                                ...tag,
                                marked_dates: [...tag.marked_dates, data.marked],
                            };
                        } else {
                            return {
                                ...tag,
                                marked_dates: tag.marked_dates.filter((m) => m.date !== date),
                            };
                        }
                    })
                );
            })
            .finally(() => setMarking((prev) => ({ ...prev, [habitId]: false })));
    };

    const handleDeleteHabit = (habitId) => {
        setDeleting((prev) => ({ ...prev, [habitId]: true }));
        const token = getAuthToken();
        fetch(`http://127.0.0.1:8000/api/habits/${habitId}`, {
            method: 'DELETE',
            headers: { Authorization: `Bearer ${token}` },
        })
            .then((res) => res.json().then((body) => ({ ok: res.ok, body })))
            .then(({ ok, body }) => {
                if (ok) setTags((prev) => prev.filter((t) => t.id !== habitId));
                else alert(body.message || 'Error deleting habit');
            })
            .finally(() => setDeleting((prev) => ({ ...prev, [habitId]: false })));
    };

    const toLocalISODate = (date) => {
        const offset = date.getTimezoneOffset();
        const local = new Date(date.getTime() - offset * 60 * 1000);
        return local.toISOString().split('T')[0];
    };

    const getMondayFromOffset = (offset) => {
        const now = new Date();
        const currentDay = now.getDay();
        const diff = now.getDate() - currentDay + (currentDay === 0 ? -6 : 1);
        const monday = new Date(now.setDate(diff + offset * 7));
        monday.setHours(0, 0, 0, 0);
        return monday;
    };

    const monday = getMondayFromOffset(weekOffset);

    return (
        <>

            <div id="header" className="section">
                <h1>Habitra</h1>
                <Clock />
            </div>

            <div id="habit-table-section" className="section">
                <h2>Habit Tracker</h2>
                <button onClick={refreshHabits} disabled={refreshing}>
                    {refreshing ? 'Odświeżanie...' : 'Odśwież'}
                </button>
                <h3>
                    <a
                        href="#"
                        onClick={(e) => {
                            e.preventDefault();
                            setWeekOffset((prev) => prev - 1);
                        }}
                    >
                        &lt;
                    </a>
                    {' '}
                    {monday.toLocaleDateString('pl-PL')}
                    {' '}
                    <a
                        href="#"
                        onClick={(e) => {
                            e.preventDefault();
                            setWeekOffset((prev) => prev + 1);
                        }}
                    >
                        &gt;
                    </a>
                </h3>

                <table>
                    <thead>
                    <tr>
                        <th className="task-column">Task</th>
                        {['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'].map((d) => (
                            <th key={d} className="day-column">{d}</th>
                        ))}
                        <th className="progress-column">Progress</th>
                    </tr>
                    </thead>
                    <tbody>
                    {tags.map((tag) => {
                        let marksThisWeek = 0;
                        return (
                            <tr key={tag.id}>
                                <td className="task-column">
                                    <div className="task-cell">
                                        <span>{tag.name}</span>
                                        <button
                                            onClick={() => handleDeleteHabit(tag.id)}
                                            className="submit-button"
                                            disabled={deleting[tag.id]}
                                        >
                                            <i className="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                                {Array.from({ length: 7 }).map((_, idx) => {
                                    const date = new Date(monday);
                                    date.setDate(monday.getDate() + idx);
                                    const iso = toLocalISODate(date);
                                    const isChecked = tag.marked_dates.some((m) => m.date === iso);
                                    if (isChecked) marksThisWeek++;
                                    return (
                                        <td key={idx} className="day-column">
                                            <input
                                                type="checkbox"
                                                checked={isChecked}
                                                disabled={marking[tag.id]}
                                                onChange={() => handleMarkDate(tag.id, iso)}
                                            />
                                        </td>
                                    );
                                })}
                                <td className="progress-column">
                                    <div className="progress">
                                        <div
                                            className="progress-bar"
                                            style={{ width: `${(marksThisWeek / 7) * 100}%` }}
                                        ></div>
                                    </div>
                                </td>
                            </tr>
                        );
                    })}
                    <tr>
                        <td colSpan="9">
                            <form onSubmit={handleNewTag} className="add-container">
                                <input
                                    name="tag"
                                    type="text"
                                    placeholder="Add a new tag..."
                                    className="task-input"
                                    value={newTag}
                                    onChange={(e) => setNewTag(e.target.value)}
                                    required
                                />
                                <button type="submit" className="submit-button" disabled={submitting}>
                                    <i className="fa-solid fa-plus"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </>
    );
}

export default Habits;