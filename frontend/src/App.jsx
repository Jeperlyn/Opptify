import { useEffect, useMemo, useState } from 'react'
import {
  createAdminEvent,
  createAdminSession,
  fetchCurrentUser,
  fetchPrograms,
  submitEmailSubscription,
} from './api'
import './App.css'

function App() {
  const [pathname, setPathname] = useState(window.location.pathname)
  const [isAdminPromptOpen, setIsAdminPromptOpen] = useState(false)
  const [adminPassword, setAdminPassword] = useState('')
  const [adminPromptError, setAdminPromptError] = useState('')
  const [isAuthorizingAdmin, setIsAuthorizingAdmin] = useState(false)

  useEffect(() => {
    const handlePopState = () => setPathname(window.location.pathname)

    window.addEventListener('popstate', handlePopState)

    return () => {
      window.removeEventListener('popstate', handlePopState)
    }
  }, [])

  function navigate(nextPath) {
    if (nextPath === window.location.pathname) {
      return
    }

    window.history.pushState({}, '', nextPath)
    setPathname(nextPath)
  }

  async function openAdminPortal() {
    setAdminPassword('')
    setAdminPromptError('')
    setIsAdminPromptOpen(true)
  }

  async function handleAdminPasswordSubmit(event) {
    event.preventDefault()

    if (!adminPassword.trim()) {
      setAdminPromptError('Enter the admin password.')
      return
    }

    setIsAuthorizingAdmin(true)
    setAdminPromptError('')

    try {
      const result = await createAdminSession(adminPassword)
      localStorage.setItem('cityjoblink_admin_token', result.token)
      setIsAdminPromptOpen(false)
      setAdminPassword('')
      navigate('/admin/events/create')
    } catch (error) {
      setAdminPromptError(error.message)
    } finally {
      setIsAuthorizingAdmin(false)
    }
  }

  if (pathname.startsWith('/admin/events/create')) {
    return <AdminCommandCenter navigate={navigate} />
  }

  return (
    <>
      <CitizenPortal onAdminClick={openAdminPortal} />

      {isAdminPromptOpen && (
        <AdminPasswordModal
          password={adminPassword}
          error={adminPromptError}
          isSubmitting={isAuthorizingAdmin}
          onClose={() => setIsAdminPromptOpen(false)}
          onPasswordChange={setAdminPassword}
          onSubmit={handleAdminPasswordSubmit}
        />
      )}
    </>
  )
}

function CitizenPortal({ onAdminClick }) {
  const [programs, setPrograms] = useState([])
  const [expandedProgramId, setExpandedProgramId] = useState(null)
  const [isLoadingPrograms, setIsLoadingPrograms] = useState(true)
  const [programError, setProgramError] = useState('')
  const [search, setSearch] = useState('')

  const [email, setEmail] = useState('')
  const [isSubmittingEmail, setIsSubmittingEmail] = useState(false)
  const [emailMessage, setEmailMessage] = useState('')
  const [emailError, setEmailError] = useState('')

  const filteredPrograms = useMemo(() => programs, [programs])

  useEffect(() => {
    let isMounted = true

    async function loadPrograms() {
      setIsLoadingPrograms(true)
      setProgramError('')

      try {
        const result = await fetchPrograms(search)

        if (!isMounted) {
          return
        }

        setPrograms(result.data ?? [])
      } catch (error) {
        if (!isMounted) {
          return
        }

        setProgramError(error.message)
      } finally {
        if (isMounted) {
          setIsLoadingPrograms(false)
        }
      }
    }

    loadPrograms()

    return () => {
      isMounted = false
    }
  }, [search])

  async function handleEmailSubmit(event) {
    event.preventDefault()

    if (!email.trim()) {
      setEmailError('Enter your email address.')
      return
    }

    setIsSubmittingEmail(true)
    setEmailError('')
    setEmailMessage('')

    try {
      const result = await submitEmailSubscription(email)
      setEmailMessage(result?.message ?? 'You are subscribed for updates.')
      setEmail('')
    } catch (error) {
      setEmailError(error.message)
    } finally {
      setIsSubmittingEmail(false)
    }
  }

  return (
    <div className="layout">
      <header className="hero">
        <p className="eyebrow">Quezon City PESO</p>
        <h1>CityJobLink Proactive Services</h1>
        <p className="subtitle">
          Explore program requirements, identify the right government focal person,
          and get timely alerts about local opportunities.
        </p>

        <button className="hero-link" type="button" onClick={onAdminClick}>
          Admin
        </button>
      </header>

      <section className="panel">
        <div className="panel-header">
          <h2>Program Directory</h2>
          <input
            value={search}
            onChange={(event) => setSearch(event.target.value)}
            placeholder="Search programs"
            aria-label="Search programs"
          />
        </div>

        {isLoadingPrograms && <p className="state">Loading directory...</p>}
        {programError && <p className="error">{programError}</p>}

        <div className="program-grid">
          {!isLoadingPrograms && filteredPrograms.length === 0 && (
            <p className="state">No programs found for your search.</p>
          )}

          {filteredPrograms.map((program) => {
            const isExpanded = expandedProgramId === program.id

            return (
              <article key={program.id} className="program-card">
                <button
                  className="program-toggle"
                  onClick={() => setExpandedProgramId(isExpanded ? null : program.id)}
                  type="button"
                >
                  <span>
                    <strong>{program.title}</strong>
                    <small>{program.category ?? 'General Program'}</small>
                  </span>
                  <span>{isExpanded ? 'Hide Details' : 'View Details'}</span>
                </button>

                {isExpanded && (
                  <div className="program-details">
                    <p>{program.description || 'No description provided.'}</p>

                    <div className="detail-block">
                      <h3>Requirements</h3>
                      <ul>
                        {(program.requirements ?? []).map((item) => (
                          <li key={`${program.id}-req-${item}`}>{item}</li>
                        ))}
                      </ul>
                    </div>

                    <div className="detail-block">
                      <h3>Steps to Avail</h3>
                      <ol>
                        {(program.steps_to_avail ?? []).map((step) => (
                          <li key={`${program.id}-step-${step}`}>{step}</li>
                        ))}
                      </ol>
                    </div>

                    <div className="contact-block">
                      <h3>Focal Person</h3>
                      <p>{program.contact?.focal_person_name ?? 'Not yet assigned'}</p>
                      <p>Desk: {program.contact?.desk_number ?? 'TBA'}</p>
                      <p>Office: {program.contact?.department_desk ?? 'TBA'}</p>
                      <p>Contact: {program.contact?.contact_details ?? 'TBA'}</p>
                    </div>
                  </div>
                )}
              </article>
            )
          })}
        </div>
      </section>

      <section className="panel">
        <h2>Want to be updated for Job Fairs and employer of the day? input your email here</h2>

        <form className="subscribe-form" onSubmit={handleEmailSubmit}>
          <label className="email-field" htmlFor="email">
            Email Address
            <input
              id="email"
              value={email}
              onChange={(event) => setEmail(event.target.value)}
              placeholder="name@example.com"
              type="email"
              required
            />
          </label>

          <button type="submit" disabled={isSubmittingEmail}>
            {isSubmittingEmail ? 'Submitting...' : 'Get Updates'}
          </button>
        </form>

        {emailMessage && <p className="success">{emailMessage}</p>}
        {emailError && <p className="error">{emailError}</p>}
      </section>
    </div>
  )
}

function AdminCommandCenter({ navigate }) {
  const [adminToken, setAdminToken] = useState(localStorage.getItem('cityjoblink_admin_token') ?? '')
  const [adminUser, setAdminUser] = useState(null)
  const [isLoadingAdminUser, setIsLoadingAdminUser] = useState(false)
  const [isSubmitting, setIsSubmitting] = useState(false)
  const [message, setMessage] = useState('')
  const [error, setError] = useState('')
  const [form, setForm] = useState({
    title: '',
    type: 'job_fair',
    description: '',
    event_date: '',
    send_email_alert: true,
  })

  useEffect(() => {
    let isMounted = true

    async function loadAdminUser() {
      if (!adminToken) {
        setAdminUser(null)
        return
      }

      setIsLoadingAdminUser(true)

      try {
        const user = await fetchCurrentUser(adminToken)

        if (!isMounted) {
          return
        }

        if (!user?.is_admin) {
          localStorage.removeItem('cityjoblink_admin_token')
          setAdminToken('')
          setAdminUser(null)
          setError('Admin access expired. Enter the password again.')
          return
        }

        setAdminUser(user)
      } catch (requestError) {
        if (isMounted) {
          localStorage.removeItem('cityjoblink_admin_token')
          setAdminToken('')
          setAdminUser(null)
          setError(requestError.message)
        }
      } finally {
        if (isMounted) {
          setIsLoadingAdminUser(false)
        }
      }
    }

    loadAdminUser()

    return () => {
      isMounted = false
    }
  }, [adminToken])

  async function handleSubmit(event) {
    event.preventDefault()

    if (!adminToken) {
      setError('Use the Admin button on the public page to unlock this area.')
      return
    }

    setIsSubmitting(true)
    setError('')
    setMessage('')

    try {
      const result = await createAdminEvent(adminToken, form)
      setMessage(result?.message ?? 'Event created successfully.')
      setForm({
        title: '',
        type: 'job_fair',
        description: '',
        event_date: '',
        send_email_alert: true,
      })
    } catch (requestError) {
      setError(requestError.message)
    } finally {
      setIsSubmitting(false)
    }
  }

  return (
    <div className="layout layout--admin">
      <header className="hero hero--admin">
        <div>
          <p className="eyebrow">PESO Admin</p>
          <h1>Command Center</h1>
          <p className="subtitle">
            Create public events and trigger email alerts from a protected admin surface.
          </p>
        </div>

        <div className="hero-actions">
          <button type="button" className="secondary-action" onClick={() => navigate('/')}>
            Back to Citizen View
          </button>
        </div>
      </header>

      <section className="panel panel--admin">
        <div className="admin-lock">
          <div>
            <h2>Protected Access</h2>
            <p className="state small">Admin access is verified with a password-backed token exchange.</p>
          </div>

          <div className="admin-status">
            {isLoadingAdminUser ? 'Verifying admin access...' : adminUser ? `Signed in as ${adminUser.name}` : 'Admin access required'}
          </div>
        </div>

        <form className="admin-form" onSubmit={handleSubmit}>
          <label>
            Event Title
            <input
              value={form.title}
              onChange={(event) => setForm((current) => ({ ...current, title: event.target.value }))}
              placeholder="Mega Job Fair at Quezon Memorial Circle"
              required
            />
          </label>

          <label>
            Event Type
            <select
              value={form.type}
              onChange={(event) => setForm((current) => ({ ...current, type: event.target.value }))}
            >
              <option value="job_fair">Job Fair</option>
              <option value="employer_of_the_day">Employer of the Day</option>
            </select>
          </label>

          <label className="admin-field--full">
            Event Details
            <textarea
              value={form.description}
              onChange={(event) => setForm((current) => ({ ...current, description: event.target.value }))}
              placeholder="Add venue, requirements, registration details, and any notes that should appear publicly."
              rows="6"
              required
            />
          </label>

          <label>
            Date & Time
            <input
              type="datetime-local"
              value={form.event_date}
              onChange={(event) => setForm((current) => ({ ...current, event_date: event.target.value }))}
              required
            />
          </label>

          <label className="toggle-switch admin-field--full">
            <input
              type="checkbox"
              checked={form.send_email_alert}
              onChange={(event) => setForm((current) => ({ ...current, send_email_alert: event.target.checked }))}
            />
            <span>
              <strong>Notify Citizens</strong>
              <small>Send email alert to opted-in users</small>
            </span>
          </label>

          <div className="inline-actions admin-actions">
            <button type="submit" disabled={isSubmitting || isLoadingAdminUser || !adminToken}>
              {isSubmitting ? 'Publishing...' : 'Submit Event'}
            </button>
          </div>
        </form>

        {message && <p className="success">{message}</p>}
        {error && <p className="error">{error}</p>}
      </section>
    </div>
  )
}

function AdminPasswordModal({ password, error, isSubmitting, onClose, onPasswordChange, onSubmit }) {
  return (
    <div className="modal-backdrop" role="presentation" onClick={onClose}>
      <div className="modal-card" role="dialog" aria-modal="true" aria-labelledby="admin-modal-title" onClick={(event) => event.stopPropagation()}>
        <h2 id="admin-modal-title">Admin</h2>
        <p>Enter the admin password to unlock the command center.</p>

        <form onSubmit={onSubmit} className="modal-form">
          <label htmlFor="admin-password">
            Password
            <input
              id="admin-password"
              type="password"
              value={password}
              onChange={(event) => onPasswordChange(event.target.value)}
              autoComplete="current-password"
              autoFocus
            />
          </label>

          <div className="inline-actions">
            <button type="button" className="secondary-action" onClick={onClose}>
              Cancel
            </button>
            <button type="submit" disabled={isSubmitting}>
              {isSubmitting ? 'Checking...' : 'Unlock'}
            </button>
          </div>
        </form>

        {error && <p className="error">{error}</p>}
      </div>
    </div>
  )
}

export default App
