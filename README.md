# 🏏 Club Management System – Bangladesh

## 📌 Project Overview

The **Club Management System BD** is a comprehensive web-based platform developed to efficiently manage and coordinate the activities of various **sports clubs in Bangladesh**.  
This system centralizes club operations, memberships, finances, tournaments, players, venues, and communications into a single, easy-to-use solution.

The project is designed to be **scalable, user-friendly, and practical**, addressing real-world challenges faced by sports clubs and organizations.

---

## 🎯 Project Goal

To develop a **comprehensive and efficient system** for managing and coordinating the activities of multiple sports clubs, enabling better organization, transparency, and collaboration.

---

## 🧩 Core Features & Modules

### 1️⃣ Club Management

#### 🏢 Club Registration
- Register new clubs with:
  - Club name
  - Address
  - Contact information
  - Sports offered
- Upload and manage club logos/photos
- Edit and update club profiles

#### 👥 Club Membership Management
- Register and manage club members
- Track membership duration:
  - Start date
  - End date
- Store member details:
  - Contact information
  - Sports preferences
- Membership renewal and tracking

#### 💰 Club Finance Management
- Record and manage:
  - Membership fees
  - Donations
  - Expenses
- Assign transactions to specific clubs
- Filter financial records by:
  - Club
  - Date range
- Generate financial summaries:
  - Total income
  - Total expenses
  - Net balance
- Export financial reports as **CSV files**
- AJAX-based transaction insertion (no page reload)

---

### 2️⃣ Tournament Management

#### 🏆 Tournament Creation
- Create tournaments with:
  - Tournament name
  - Sport type
  - Format
  - Start & end dates
  - Venue
- Manage tournament schedules and fixtures

#### 👕 Team Registration
- Clubs can register teams for tournaments
- Each team:
  - Belongs to a club
  - Has a team captain
- Manage team rosters and player assignments

#### 🗓 Match Scheduling
- Schedule matches with:
  - Date & time
  - Venue
  - Referees
- Manage match fixtures

#### 📊 Results Management
- Record match results:
  - Scores
  - Goals
  - Relevant match statistics
- Generate:
  - Tournament standings
  - Match reports

---

### 3️⃣ Player Management

#### 🧍 Player Registration
- Register players with:
  - Name
  - Date of Birth
  - Contact information
  - Sports experience
  - Profile photo
- Full **CRUD operations** (Create, Read, Update, Delete)

#### 📈 Player Performance Tracking
- Track player statistics such as:
  - Goals
  - Assists
  - Performance metrics
- Generate player performance reports
- Ensure:
  - A player cannot be assigned to multiple teams simultaneously

---

### 4️⃣ Venue Management

#### 🏟 Venue Registration
- Register sports venues with:
  - Venue name
  - Address
  - Contact information
  - Available facilities
- Manage venue availability
- Handle venue bookings for matches and tournaments

---

### 5️⃣ Communication & Collaboration

#### 📰 News & Announcements
- Publish system-wide news and announcements
- Clubs can:
  - Post their own news
  - Share upcoming events
- Centralized communication platform for clubs, players, and officials

---

## 🛠️ Technology Stack

| Technology | Usage |
|----------|------|
| **PHP** | Backend logic |
| **MySQL** | Database management |
| **Bootstrap 5** | Responsive UI |
| **JavaScript / jQuery** | Dynamic interactions & AJAX |
| **HTML5 / CSS3** | Frontend structure & styling |

---

## 🗄️ Database Overview

Key database tables include:
- `Club`
- `Member`
- `Club_member`
- `Finance`
- `Player`
- `Tournament`
- `Match_`
- `Venue`
- (Additional relational tables for teams and assignments)

Example `Finance` table:
```sql
Finance (
  Finance_id,
  Club_id,
  Membership_fees,
  Donations,
  Expenses,
  Transaction_date
)
