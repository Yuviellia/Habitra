import { useEffect, useState } from 'react';

function Habits() {
    const [tags, setTags] = useState([]);
    const [newTag, setNewTag] = useState('');
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [submitting, setSubmitting] = useState(false);
    const [notFound, setNotFound] = useState(false);
    const [marking, setMarking] = useState({});
    const [deleting, setDeleting] = useState({});

    const getAuthToken = () => localStorage.getItem("token");

    const fetchHabits = () => {
        setRefreshing(true);
        const token = getAuthToken();
        fetch(`http://127.0.0.1:8000/api/habits`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            }
        })
            .then((res) => {
                if (res.status === 404) {
                    setTags([]);
                    setNotFound(true);
                    return;
                }
                if (!res.ok) throw new Error('Błąd pobierania nawyków');
                return res.json();
            })
            .then((data) => {
                if (data) {
                    setTags(data);
                    setNotFound(false);
                }
            })
            .catch(() => setNotFound(true))
            .finally(() => {
                setLoading(false);
                setRefreshing(false);
            });
    };

    useEffect(() => {
        fetchHabits();
    }, []);

    const refreshHabits = () => fetchHabits();

    const handleMarkDate = (habitId, date) => {
        setMarking((prev) => ({ ...prev, [habitId]: true }));
        const token = getAuthToken();
        fetch(`http://127.0.0.1:8000/api/habits/${habitId}/mark`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ date }),
        })
            .then((res) => res.json())
            .then((data) => {
                setTags(tags.map(tag =>
                    tag.id === habitId
                        ? { ...tag, marked_dates: [...tag.marked_dates, data.marked] }
                        : tag
                ));
            })
            .finally(() => setMarking((prev) => ({ ...prev, [habitId]: false })));
    };

    const handleNewTag = (e) => {
        e.preventDefault();
        setSubmitting(true);
        const token = getAuthToken();
        fetch(`http://127.0.0.1:8000/api/habits`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ name: newTag }),
        })
            .then((res) => res.json())
            .then((data) => {
                setTags([...tags, { ...data.habit, marked_dates: [] }]);
                setNewTag('');
                setNotFound(false);
            })
            .finally(() => setSubmitting(false));
    };

    const handleDeleteMark = (habitId, markId) => {
        if (!window.confirm("Czy na pewno chcesz usunąć to oznaczenie?")) return;
        setDeleting((prev) => ({ ...prev, [`mark-${markId}`]: true }));
        const token = getAuthToken();

        fetch(`http://127.0.0.1:8000/api/habits/${habitId}/mark/${markId}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${token}`,
            }
        })
            .then((res) => {
                if (res.ok) {
                    setTags(tags.map(tag =>
                        tag.id === habitId
                            ? {
                                ...tag,
                                marked_dates: tag.marked_dates.filter(m => m.id !== markId)
                            }
                            : tag
                    ));
                }
            })
            .finally(() => setDeleting((prev) => ({ ...prev, [`mark-${markId}`]: false })));
    };

    const handleDeleteHabit = (habitId) => {
        if (!window.confirm("Czy na pewno chcesz usunąć ten nawyk?")) return;
        setDeleting((prev) => ({ ...prev, [habitId]: true }));
        const token = getAuthToken();
        fetch(`http://127.0.0.1:8000/api/habits/${habitId}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${token}`
            }
        })
            .then((res) => {
                if (res.ok) {
                    setTags(tags.filter(tag => tag.id !== habitId));
                }
            })
            .finally(() => setDeleting((prev) => ({ ...prev, [habitId]: false })));
    };

    const handleDeleteAllMarks = (habitId) => {
        if (!window.confirm("Czy na pewno chcesz usunąć wszystkie oznaczenia tego nawyku?")) return;
        setDeleting((prev) => ({ ...prev, [`marks-${habitId}`]: true }));
        const token = getAuthToken();
        fetch(`http://127.0.0.1:8000/api/habits/${habitId}/marks`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${token}`
            }
        })
            .then((res) => {
                if (res.ok) {
                    setTags(tags.map(tag =>
                        tag.id === habitId ? { ...tag, marked_dates: [] } : tag
                    ));
                }
            })
            .finally(() => setDeleting((prev) => ({ ...prev, [`marks-${habitId}`]: false })));
    };

    return (
        <div>
            <h2>Twoje Nawyki</h2>

            <button onClick={refreshHabits} disabled={refreshing}>
                {refreshing ? 'Odświeżanie...' : 'Odśwież'}
            </button>

            <form onSubmit={handleNewTag}>
                <input
                    type="text"
                    placeholder="Nazwa nowego tagu"
                    value={newTag}
                    onChange={(e) => setNewTag(e.target.value)}
                    required
                />
                <button type="submit" disabled={submitting}>
                    {submitting ? 'Dodawanie...' : 'Dodaj tag'}
                </button>
            </form>

            {!notFound && tags.length > 0 && (
                <ul>
                    {tags.map(tag => (
                        <li key={tag.id}>
                            <strong>{tag.name}</strong> (Utworzono: {tag.created_at})
                            <ul>
                                {tag.marked_dates?.map(mark => (
                                    <li key={mark.id}>
                                        {mark.date}
                                        <button
                                            onClick={() => handleDeleteMark(tag.id, mark.id)}
                                            disabled={deleting[`mark-${mark.id}`]}
                                            style={{marginLeft: '8px', color: 'red'}}
                                        >
                                            {deleting[`mark-${mark.id}`] ? 'Usuwanie...' : 'Usuń'}
                                        </button>
                                    </li>
                                ))}
                            </ul>
                            <form
                                onSubmit={(e) => {
                                    e.preventDefault();
                                    const date = e.target.elements.date.value;
                                    handleMarkDate(tag.id, date);
                                }}
                            >
                                <input type="date" name="date" required/>
                                <button type="submit" disabled={marking[tag.id]}>
                                    {marking[tag.id] ? 'Oznaczanie...' : 'Dodaj oznaczenie'}
                                </button>
                            </form>

                            <button
                                onClick={() => handleDeleteHabit(tag.id)}
                                disabled={deleting[tag.id]}
                                style={{color: 'red', marginTop: '5px'}}
                            >
                                {deleting[tag.id] ? 'Usuwanie...' : 'Usuń nawyk'}
                            </button>
                        </li>
                    ))}
                </ul>
            )}
        </div>
    );
}

export default Habits;
