import React, { useEffect, useState } from 'react';
import Clock from "../components/Clock.jsx";

function Users() {
    const [users, setUsers] = useState([]);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [error, setError] = useState(null);

    const getAuthToken = () => localStorage.getItem("token") || '';

    const fetchUsers = () => {
        setRefreshing(true);
        setError(null);

        fetch('http://127.0.0.1:8000/api/users', {
            headers: {
                'Authorization': `Bearer ${getAuthToken()}`,
                'Content-Type': 'application/json',
            },
        })
            .then(async (res) => {
                // 1️⃣ If the HTTP status is 404, read the JSON and throw its "message"
                if (res.status === 404) {
                    const errorJson = await res.json().catch(() => ({}));
                    throw new Error(errorJson.message || 'Nie znaleziono użytkowników');
                }

                // 2️⃣ If it’s not a “200 OK”, but some other error (401, 500, etc.), read JSON→ message
                if (!res.ok) {
                    const errorJson = await res.json().catch(() => ({}));
                    throw new Error(errorJson.message || 'Błąd serwera');
                }

                // 3️⃣ At this point res.ok === true (status 200). The body is just an ARRAY of users:
                return res.json();
            })
            .then((dataArray) => {
                // `dataArray` is already an array of {id, email, role}
                setUsers(dataArray);
            })
            .catch((e) => {
                setError(e.message);
                setUsers([]);
            })
            .finally(() => {
                setLoading(false);
                setRefreshing(false);
            });
    };

    useEffect(() => {
        fetchUsers();
    }, []);

    return (
        <>
            <div id="header" className="section">
                <h1>Habitra</h1>
                <Clock/>
            </div>
            <div id="todo-section" className="section">
                <h2>User List &nbsp;
                    <i
                        onClick={!refreshing ? fetchUsers : undefined}
                        className={`fa-solid fa-arrows-rotate ${refreshing ? 'disabled' : ''}`}

                    ></i>
                </h2>

                {error && <p style={{color: 'red'}}>{error}</p>}

                {!loading && !error && users.length > 0 ? (
                    <ul className="task-list">
                        {users.map(u => (
                            <li key={u.id}>
                                <span>{u.email}</span>
                            </li>
                        ))}
                    </ul>
                ) : (
                    !loading && !error && <p>Brak użytkowników do wyświetlenia.</p>
                )}
            </div>
        </>

        /*
            {!notFound && todos.length > 0 && (
                <ul className="task-list">
                    {todos.map(todo => (
                        <li key={todo.id} style={{marginBottom: '8px'}}>
                        <span>{todo.task}</span>
                                    <button
                                        onClick={() => handleDeleteTodo(todo.id)}
                                        disabled={deleting[todo.id]}
                                        className="submit-button"
                                    >
                                        <i className="fa-solid fa-trash"></i>
                                    </button>
                                </li>
                            ))}
                        </ul>
                    )}
                </div>
            </>*/
    );
}

export default Users;
