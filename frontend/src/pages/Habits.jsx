import { useEffect, useState } from 'react';

function Habits() {
    const userId = 1;
    const [tags, setTags] = useState([]);
    const [newTag, setNewTag] = useState('');
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [submitting, setSubmitting] = useState(false);
    const [notFound, setNotFound] = useState(false);
    const [marking, setMarking] = useState({}); // { habitId: true/false }

    const getAuthToken = () => {
        // Retrieve the token from localStorage or sessionStorage
        return localStorage.getItem("token"); // Adjust if you use sessionStorage or another storage method
    };

    const fetchHabits = () => {
        setRefreshing(true);

        const token = getAuthToken(); // Get token for authentication
        fetch(`http://127.0.0.1:8000/api/user/${userId}/habits`, {
            headers: {
                'Authorization': `Bearer ${token}`, // Add Bearer token
                'Content-Type': 'application/json'
            }
        })
            .then((res) => {
                if (res.status === 404) {
                    setTags([]); // No habits found
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

    const refreshHabits = () => {
        fetchHabits();
    };

    const handleNewTag = (e) => {
        e.preventDefault();
        setSubmitting(true);

        const token = getAuthToken(); // Get token for authentication
        fetch(`http://127.0.0.1:8000/api/user/${userId}/habits`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`, // Add Bearer token
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ name: newTag }),
        })
            .then((res) => res.json())
            .then((data) => {
                setTags([...tags, { ...data.habit, marked_dates: [] }]); // Ensure empty marked_dates array
                setNewTag('');
                setNotFound(false);
            })
            .finally(() => setSubmitting(false));
    };

    const handleMarkDate = (habitId, date) => {
        setMarking((prev) => ({ ...prev, [habitId]: true }));

        const token = getAuthToken(); // Get token for authentication
        fetch(`http://127.0.0.1:8000/api/user/${userId}/habits/${habitId}/mark`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`, // Add Bearer token
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
                    {tags.map((tag) => (
                        <li key={tag.id}>
                            <strong>{tag.name}</strong> (Utworzono: {tag.created_at})
                            <ul>
                                {tag.marked_dates?.map((mark) => (
                                    <li key={mark.id}>{mark.date}</li>
                                ))}
                            </ul>
                            <form
                                onSubmit={(e) => {
                                    e.preventDefault();
                                    const date = e.target.elements.date.value;
                                    handleMarkDate(tag.id, date);
                                }}
                            >
                                <input type="date" name="date" required />
                                <button type="submit" disabled={marking[tag.id]}>
                                    {marking[tag.id] ? 'Oznaczanie...' : 'Dodaj oznaczenie'}
                                </button>
                            </form>
                        </li>
                    ))}
                </ul>
            )}
        </div>
    );
}

export default Habits;
