# Contractly - Features & Capabilities

## Overview

Contractly is an AI-powered contract analysis platform that helps users understand, track, and manage their contracts.

---

## MVP Features

### 1. User Authentication

**Status:** Planned

**Description:**
Secure user registration and authentication system.

**User Stories:**
- As a user, I can register with email and password
- As a user, I can log in to access my contracts
- As a user, I can reset my password if forgotten
- As a user, I can log out securely

**Acceptance Criteria:**
- [ ] Email/password registration with validation
- [ ] Email verification required
- [ ] Secure login with rate limiting
- [ ] Password reset via email
- [ ] Session management with Redis
- [ ] CSRF protection on all forms

---

### 2. Contract Upload

**Status:** Planned

**Description:**
Upload PDF contracts for AI analysis.

**User Stories:**
- As a user, I can upload a PDF contract
- As a user, I can see upload progress
- As a user, I can name/title my contract
- As a user, I receive feedback if upload fails

**Acceptance Criteria:**
- [ ] Accept PDF files only
- [ ] Maximum file size: 10MB
- [ ] Maximum pages: 50
- [ ] File validation (MIME type, extension)
- [ ] Secure storage with encryption
- [ ] Progress indicator during upload
- [ ] Clear error messages for failures

**Technical Notes:**
- Files stored in S3-compatible storage
- SHA-256 hash calculated for integrity
- Virus scanning consideration for production

---

### 3. AI Contract Analysis

**Status:** Planned

**Description:**
Automated analysis of contract content using Claude AI.

**User Stories:**
- As a user, I can see my contract being analyzed
- As a user, I receive a plain-language summary
- As a user, I can see categorized clauses
- As a user, I can see risk levels for clauses
- As a user, I understand why a clause is flagged

**Acceptance Criteria:**
- [ ] Processing status shown (pending → processing → completed)
- [ ] Analysis completes within 60 seconds (typical)
- [ ] Plain-language summary (3-5 sentences)
- [ ] Clause categorization:
  - Payment terms
  - Termination conditions
  - Liability/indemnification
  - Penalties
  - Auto-renewal
  - Non-compete
  - Confidentiality
  - Dispute resolution
- [ ] Risk levels: none, low, medium, high
- [ ] Risk explanation for flagged clauses
- [ ] Graceful handling of analysis failures

**Technical Notes:**
- Uses Anthropic Claude API
- Async processing via Laravel Queue
- Structured prompts for consistent output
- Response parsed into structured data

---

### 4. Deadline Extraction

**Status:** Planned

**Description:**
Automatically identify important dates and deadlines in contracts.

**User Stories:**
- As a user, I can see all deadlines in my contract
- As a user, I understand what each deadline means
- As a user, I can see recurring deadlines identified

**Acceptance Criteria:**
- [ ] Extract deadline types:
  - Payment due dates
  - Renewal dates
  - Termination notice periods
  - Delivery dates
  - Review periods
- [ ] Show source text for each deadline
- [ ] Identify recurring vs one-time deadlines
- [ ] Handle relative dates ("30 days after signing")

---

### 5. Email Reminders

**Status:** Planned

**Description:**
Set reminders for contract deadlines delivered via email.

**User Stories:**
- As a user, I can set a reminder for any deadline
- As a user, I can choose how many days before to be reminded
- As a user, I receive email reminders on schedule
- As a user, I can cancel or modify reminders

**Acceptance Criteria:**
- [ ] Create reminder for extracted deadlines
- [ ] Create custom reminder for any date
- [ ] Reminder intervals: 30, 14, 7, 3, 1 days before
- [ ] Email delivery with contract context
- [ ] Reminder management (view, edit, delete)
- [ ] Timezone-aware scheduling

**Technical Notes:**
- Laravel Scheduler for daily reminder check
- Queue-based email sending
- Resend/SendGrid for production delivery

---

### 6. Contract Management

**Status:** Planned

**Description:**
View and manage uploaded contracts.

**User Stories:**
- As a user, I can see all my uploaded contracts
- As a user, I can view details of any contract
- As a user, I can delete contracts I no longer need
- As a user, I can search/filter my contracts

**Acceptance Criteria:**
- [ ] List view with pagination
- [ ] Contract details page with full analysis
- [ ] Delete contract (soft delete)
- [ ] Filter by status, risk level
- [ ] Sort by date, name
- [ ] Search by title/filename

---

## Post-MVP Features

### 7. Translation & Summary

**Status:** Future

**Description:**
Summarize contracts written in foreign languages into English.

**User Stories:**
- As a user, I can upload a contract in another language
- As a user, I receive an English summary (not full translation)
- As a user, I can see key points translated

**Notes:**
- Support languages: German, French, Spanish, Italian initially
- Summary-focused, not word-for-word translation
- Highlight key terms and obligations

---

### 8. WhatsApp Reminders

**Status:** Future

**Description:**
Receive deadline reminders via WhatsApp.

**User Stories:**
- As a user, I can opt to receive reminders via WhatsApp
- As a user, I can link my WhatsApp number
- As a user, I receive formatted reminder messages

**Notes:**
- WhatsApp Business API integration
- Requires business verification
- Additional cost consideration

---

### 9. Dashboard & Analytics

**Status:** Future

**Description:**
Overview dashboard with contract statistics and upcoming deadlines.

**User Stories:**
- As a user, I see a summary of my contracts on login
- As a user, I see upcoming deadlines at a glance
- As a user, I see risk distribution across contracts

---

### 10. Team/Organization Features

**Status:** Future

**Description:**
Share contracts and collaborate with team members.

**Notes:**
- Organization/workspace model
- Role-based permissions
- Shared contract library
- Activity audit log

---

## Feature Dependencies

```
Authentication
     │
     ▼
Contract Upload ──────────────────┐
     │                            │
     ▼                            ▼
AI Analysis ◄──────────────► Deadline Extraction
     │                            │
     ▼                            ▼
Contract Management          Email Reminders
```

---

## Success Metrics

| Feature | Metric | Target |
|---------|--------|--------|
| Upload | Success rate | > 95% |
| Analysis | Completion time | < 60s |
| Analysis | Accuracy (user feedback) | > 80% helpful |
| Reminders | Delivery rate | > 99% |
| Retention | Return users | > 30% upload 2nd contract |
