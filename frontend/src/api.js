const API_BASE_URL = import.meta.env.VITE_API_BASE_URL ?? 'http://127.0.0.1:8000/api';

async function toJson(response) {
  const payload = await response.json().catch(() => ({}));

  if (!response.ok) {
    const message = payload?.message ?? 'Request failed.';
    throw new Error(message);
  }

  return payload;
}

export async function fetchPrograms(search = '') {
  const params = new URLSearchParams();
  if (search) {
    params.set('search', search);
  }

  const query = params.toString();
  const response = await fetch(`${API_BASE_URL}/programs${query ? `?${query}` : ''}`);

  return toJson(response);
}

export async function fetchPreferences(token) {
  const response = await fetch(`${API_BASE_URL}/users/me/preferences`, {
    headers: {
      Accept: 'application/json',
      Authorization: `Bearer ${token}`,
    },
  });

  return toJson(response);
}

export async function updatePreferences(token, body) {
  const response = await fetch(`${API_BASE_URL}/users/me/preferences`, {
    method: 'PUT',
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
      Authorization: `Bearer ${token}`,
    },
    body: JSON.stringify(body),
  });

  return toJson(response);
}

export async function fetchCurrentUser(token) {
  const response = await fetch(`${API_BASE_URL}/user`, {
    headers: {
      Accept: 'application/json',
      Authorization: `Bearer ${token}`,
    },
  });

  return toJson(response);
}

export async function createAdminEvent(token, body) {
  const response = await fetch(`${API_BASE_URL}/admin/events`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
      Authorization: `Bearer ${token}`,
    },
    body: JSON.stringify(body),
  });

  return toJson(response);
}

export async function submitEmailSubscription(email) {
  const response = await fetch(`${API_BASE_URL}/email-subscriptions`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
    },
    body: JSON.stringify({ email }),
  });

  return toJson(response);
}

export async function createAdminSession(password) {
  const response = await fetch(`${API_BASE_URL}/admin/session`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
    },
    body: JSON.stringify({ password }),
  });

  return toJson(response);
}
