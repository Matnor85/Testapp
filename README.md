CREATE TABLE users (
id INT AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(100) NOT NULL,
card_number VARCHAR(16) UNIQUE NOT NULL,
pin_hash VARCHAR(255) NOT NULL,
role ENUM('user', 'admin') DEFAULT 'user',
created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE accounts (
id INT AUTO_INCREMENT PRIMARY KEY,
user_id INT NOT NULL,
account_type ENUM('checking', 'savings') NOT NULL,
balance DECIMAL(10,2) DEFAULT 0.00,
created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE transactions (
id INT AUTO_INCREMENT PRIMARY KEY,
type ENUM('deposit', 'withdrawal', 'transfer', 'payment') NOT NULL,
amount DECIMAL(10,2) NOT NULL,
description VARCHAR(255),
from_account_id INT,
to_account_id INT,
created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (from_account_id) REFERENCES accounts(id),
FOREIGN KEY (to_account_id) REFERENCES accounts(id)
);

CREATE TABLE bills (
id INT AUTO_INCREMENT PRIMARY KEY,
account_id INT NOT NULL,
payee VARCHAR(100) NOT NULL,
amount DECIMAL(10,2) NOT NULL,
due_date DATE NOT NULL,
paid BOOLEAN DEFAULT FALSE,
created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (account_id) REFERENCES accounts(id)
);

CREATE TABLE audit_log (
id INT AUTO_INCREMENT PRIMARY KEY,
user_id INT,
action VARCHAR(100) NOT NULL,
details TEXT,
ip_address VARCHAR(45),
created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

users, accounts, transactions, bills, audit_log

ATM /  
|  
├── public/ -- Webbserverns rot — enda mappen som är publik
│ ├── assets/ -- CSS, JS, bilder — statiska filer
│ │ ├── css/  
│ │ │ |── camera.css  
│ │ │ |── main.css  
| | | └── style.css  
| | |  
│ │ ├── js/  
│ │ │ |── atm-overview.js  
| | | |── camera.js  
| | | |── matrix.js  
| | | |── real-atm.js  
| | | └── router.js  
| | |  
│ │ └── img/  
| |  
│ └── index.php -- Tar emot alla requests, skickar vidare till Router # Router
|  
├── src/ -- All PHP-logik — aldrig direkt åtkomlig från webben
| ├── Interface/ -- Kontrakt — tvingar klasser att ha rätt metoder
| │ ├── RepositoryInterface.php  
| │ └── ServiceInterface.php  
| |  
| ├── Repository/ # Bara SQL -- Bara SQL med PDO — inga affärsregler här
│ | ├── UserRepository.php  
| | ├── AccountRepository.php  
| | ├── TransactionRepository.php  
| | ├── BillRepository.php  
| | └── AuditRepository.php  
| |  
│ ├── Service / -- Affärslogik — saldokontroll, validering, regler
| | ├── UserService.php  
| | ├── AccountService.php  
| | ├── TransferService.php  
| | ├── BillService.php  
| | └── AuditService.php  
| |  
│ ├── Model / -- Dataobjekt — User, Account, Transaction, Bill, AuditLog
│ | ├── User.php  
│ | ├── Account.php  
│ | ├── Transaction.php  
| | ├── Bill.php  
| | └── AuditLog.php  
| |  
| ├── Middleware/ -- Auth + rollkontroll — körs innan varje route
| │ ├── AuthMiddleware.php # requireAuth()
| │ └── RoleMiddleware.php # require_role()
| |  
| ├── Security/ -- CSRF-token — skyddar formulär mot angrepp
| │ └── CsrfToken.php # csrf_token()
| |  
| |── View/ -- Escape.php — htmlspecialchars() mot XSS
| | └── Escape.php # escape()
| |  
| ├── Router.php -- Matchar URL → rätt Service/template
| ├── Container.php -- Bygger upp alla objekt och kopplar ihop dem
| └── Database.php -- PDO-uppkoppling — ersätter gamla db.php
|  
├── templates/ -- HTML-vyer — tar emot färdiga Model-objekt
│ ├── admin/ -- Skyddade vyer — kräver admin-roll
│ | ├── dashboard.php # statistik
│ | ├── users.php # lista + CRUD
│ | ├── accounts.php  
│ | ├── transactions.php # filtrering + paginering + CSV-export
│ | └── audit_log.php  
│ ├── shared/ -- HTML - Komponenter
│ | ├── atm_card.php  
│ | ├── atm_cardinformation.php  
│ | ├── atm_fn_buttons.php  
│ | ├── atm_num_buttons.php  
│ | ├── atm_screen.php  
│ | └── atm_screen.php  
| |  
│ ├── around-the-corner--closed-door.php # gemensam header/footer
│ ├── around-the-corner--open-door.php # gemensam header/footer
│ ├── atm-page-digital.php # gemensam header/footer
│ ├── bills.php  
│ ├── camera.php  
│ ├── change_pin.php  
│ ├── dashboard.php # saldo + snabblänkar
│ ├── deposit.php  
│ ├── history.php # transaktionshistorik med paginering
│ ├── home.php # transaktionshistorik med paginering
│ ├── layout.php # gemensam header/footer
│ ├── login.php  
│ ├── real-atm-nr1.php  
│ ├── real-atm.php  
│ ├── shells.php  
│ ├── transfer.php  
│ └── withdraw.php  
|
├── schema.sql -- Databasstruktur — users, accounts, transactions, bills
├── seed.php -- Skapar testanvändare med hashade PIN-koder
|── README.md
├── .env -- Hemliga uppgifter — checkas INTE in i git
├── .env.example --
└── .gitignore -- Blockerar .env från att hamna på GitHub

Router → Service → Repository → Model
↓
Template (tar emot färdiga Model-objekt)

Flöde: en request genom systemet
index.php ----> Router ----> Middleware ----> Service ----> Repository ----> Template  
request in matchar URL auth + roll affärslogik SQL + PDO HTML ut

=== kod exempel ===

// EF i C# skulle se ut så här:
var user = context.Users.Find(1);

// Din PHP med Repository ser nästan likadant ut:
$user = $userRepository->findById(1);
// Returnerar ett User-objekt, precis som EF

(kortnummer) # Färgkodning — helt vettigt som ett UX-lager i prototypmiljön ovanpå riktiga kontodata. Ingen funktionell logik i det, bara visuell hjälp för dig under utveckling.

Stoppa in kort  
 ↓  
Ange Språk  
 ↓  
Ange PIN  
 ↓  
Huvudmeny  
 ├── Saldo  
 │ ├── Lönekonto  
 │ ├── Sparkonto  
 │ └── Om fler sparkonton  
 |  
 ├── Uttag (bara från lönekonto)
| ├── Uttag 100 (Sker direkt)
│ ├── Uttag 200 (Sker direkt)
│ ├── Uttag 500 (Sker direkt)
│ └── Annan summa  
 |  
 ├── Snabbuttag 500 (bara från lönekonto)
│ └── Uttag 500 (Sker direkt)
|  
 ├── Insättning (välj konto)
| └── Summa  
 | └── Konto  
 | |── Lönekonto  
 | |── Sparkonto  
 | └── Om fler sparkonton  
 |  
 └── Fler Tjänster  
 |──Kontouppgifter  
 | └── (Visa namn + kontonummer + konto saldo)  
 |  
 |── Överföring (mellan egna konton) Från (\***\*)konto till (\*\***)konto ----> Summa  
 | ├── Lönekonto  
 | ├── Sparkonto  
 | └── Om fler sparkonton  
 |  
 ├── Betalning  
 | └── Fakturor (val av faktura) Från (\*\*\*\*)konto summa = faktura summan  
 | └── Konto val  
 |  
 └── PIN-byte  
 └── (PIN kods byte)
