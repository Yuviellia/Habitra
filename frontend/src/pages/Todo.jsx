import { useEffect, useState } from 'react';

function Todos() {
    const userId = 1;
    const [todos, setTodos] = useState([]);
    const [newTodo, setNewTodo] = useState('');
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [submitting, setSubmitting] = useState(false);
    const [notFound, setNotFound] = useState(false);

    // Function to retrieve the authentication token
    const getAuthToken = () => {
        return localStorage.getItem('token'); // Retrieve token from localStorage
    };

    const fetchTodos = () => {
        setRefreshing(true);

        const token = getAuthToken(); // Get the token for authentication
        fetch(`http://127.0.0.1:8000/api/user/${userId}/todos`, {
            headers: {
                'Authorization': `Bearer ${token}`, // Add Bearer token for authentication
                'Content-Type': 'application/json',
            },
        })
            .then((res) => {
                if (res.status === 404) {
                    setTodos([]); // No todos found
                    setNotFound(true);
                    return;
                }
                if (!res.ok) throw new Error('Błąd pobierania listy zadań');
                return res.json();
            })
            .then((data) => {
                if (data) {
                    setTodos(data);
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
        fetchTodos();
    }, []);

    const refreshTodos = () => {
        fetchTodos();
    };

    const handleNewTodo = (e) => {
        e.preventDefault();
        setSubmitting(true);

        const token = getAuthToken(); // Get token for authentication
        fetch(`http://127.0.0.1:8000/api/user/${userId}/todos`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`, // Add Bearer token for authentication
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ task: newTodo }),
        })
            .then((res) => res.json())
            .then((data) => {
                setTodos([...todos, data.todo]); // Append new todo
                setNewTodo('');
                setNotFound(false);
            })
            .finally(() => setSubmitting(false));
    };

    return (
        <div>
            <h2>Twoje Zadania</h2>

            <button onClick={refreshTodos} disabled={refreshing}>
                {refreshing ? 'Odświeżanie...' : 'Odśwież'}
            </button>

            <form onSubmit={handleNewTodo}>
                <input
                    type="text"
                    placeholder="Nowe zadanie"
                    value={newTodo}
                    onChange={(e) => setNewTodo(e.target.value)}
                    required
                />
                <button type="submit" disabled={submitting}>
                    {submitting ? 'Dodawanie...' : 'Dodaj zadanie'}
                </button>
            </form>

            {!notFound && todos.length > 0 && (
                <ul>
                    {todos.map((todo) => (
                        <li key={todo.id}>
                            <strong>{todo.task}</strong> (Dodano: {todo.createdAt})
                        </li>
                    ))}
                </ul>
            )}
        </div>
    );
}

export default Todos;
