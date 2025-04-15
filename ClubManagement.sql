-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 07, 2025 at 08:28 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ClubManagement`
--

-- --------------------------------------------------------

--
-- Table structure for table `Club`
--

CREATE TABLE `Club` (
  `Club_id` int(11) NOT NULL,
  `Club_name` varchar(100) DEFAULT NULL,
  `Club_address` varchar(255) DEFAULT NULL,
  `Club_contact_info` varchar(100) DEFAULT NULL,
  `Sports_offered` text DEFAULT NULL,
  `Club_logo` varchar(255) DEFAULT NULL,
  `Club_registration_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Club`
--

INSERT INTO `Club` (`Club_id`, `Club_name`, `Club_address`, `Club_contact_info`, `Sports_offered`, `Club_logo`, `Club_registration_date`) VALUES
(1, 'FC Barcelona', 'Barcelona', '+34 93 496 36 00', 'Football, Basketball, Futsal, Handball, and Roller Hockey', 'FC_Barcelona_(crest).svg', '2000-01-01'),
(2, 'Real Madrid CF', 'Madrid, Spain', '+34 91 398 43 00', 'Football, Basketball', 'rma_logo.png', '2000-01-01'),
(3, 'Manchester United F.C', 'Sir Matt Busby Way, Old Trafford, Manchester, M16 0RA', '+44 161 676 7770', 'Football', 'Manchester_United_FC_crest.svg.png', '2000-01-01'),
(4, 'Manchester City F.C.', 'Etihad Stadium, Etihad Campus, Manchester, M11 3FF', '+44 (0) 161 444 1894', 'Football', 'Manchester_City_FC_badge.svg.png', '2000-01-01');

-- --------------------------------------------------------

--
-- Table structure for table `Club_member`
--

CREATE TABLE `Club_member` (
  `Club_id` int(11) DEFAULT NULL,
  `Member_id` int(11) DEFAULT NULL,
  `Membership_start_date` date DEFAULT NULL,
  `Membership_end_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Club_member`
--

INSERT INTO `Club_member` (`Club_id`, `Member_id`, `Membership_start_date`, `Membership_end_date`) VALUES
(2, 1, '2025-04-01', '2025-04-30'),
(1, 2, '2025-04-02', '2025-05-01'),
(4, 3, '2025-04-08', '2025-05-08');

-- --------------------------------------------------------

--
-- Table structure for table `Finance`
--

CREATE TABLE `Finance` (
  `Finance_id` int(11) NOT NULL,
  `Club_id` int(11) DEFAULT NULL,
  `Membership_fees` decimal(10,2) DEFAULT NULL,
  `Donations` decimal(10,2) DEFAULT NULL,
  `Expenses` decimal(10,2) DEFAULT NULL,
  `Transaction_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Finance`
--

INSERT INTO `Finance` (`Finance_id`, `Club_id`, `Membership_fees`, `Donations`, `Expenses`, `Transaction_date`) VALUES
(7, 3, 0.00, 4000.00, 0.00, '2025-04-08'),
(8, 4, 0.00, 0.00, 2000.00, '2025-04-06'),
(11, 1, 5000.00, 0.00, 0.00, '2025-04-06'),
(12, 1, 4000.00, 0.00, 0.00, '2025-04-06'),
(13, 4, 0.00, 0.00, 1500.00, '2025-04-06'),
(14, 1, 0.00, 0.00, 6000.00, '2025-04-07'),
(15, 4, 20000.00, 0.00, 0.00, '2025-04-06'),
(16, 4, 0.00, 3500.00, 0.00, '2025-04-06'),
(17, 2, 10000.00, 0.00, 0.00, '2025-04-06');

-- --------------------------------------------------------

--
-- Table structure for table `Match_`
--

CREATE TABLE `Match_` (
  `Match_id` int(11) NOT NULL,
  `Tournament_id` int(11) DEFAULT NULL,
  `Team1_id` int(11) DEFAULT NULL,
  `Team2_id` int(11) DEFAULT NULL,
  `Match_date` date DEFAULT NULL,
  `referee` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Match_`
--

INSERT INTO `Match_` (`Match_id`, `Tournament_id`, `Team1_id`, `Team2_id`, `Match_date`, `referee`) VALUES
(2, 3, 5, 6, '2025-04-01', 'N/A'),
(3, 3, 4, 7, '2025-04-08', 'N/A');

-- --------------------------------------------------------

--
-- Table structure for table `Member`
--

CREATE TABLE `Member` (
  `Member_id` int(11) NOT NULL,
  `Member_name` varchar(100) DEFAULT NULL,
  `member_contact_info` varchar(100) DEFAULT NULL,
  `Sports_Preferences` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Member`
--

INSERT INTO `Member` (`Member_id`, `Member_name`, `member_contact_info`, `Sports_Preferences`) VALUES
(1, 'Refat', '01832-433260', 'Football'),
(2, 'Siam', '01000001111', 'Football'),
(3, 'AlAmin', '01000001111', 'Football');

-- --------------------------------------------------------

--
-- Table structure for table `Message`
--

CREATE TABLE `Message` (
  `Message_id` int(11) NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `receiver_id` int(11) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `News`
--

CREATE TABLE `News` (
  `News_id` int(11) NOT NULL,
  `Club_id` int(11) DEFAULT NULL,
  `Title` varchar(255) DEFAULT NULL,
  `Content` text DEFAULT NULL,
  `Publish_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `News`
--

INSERT INTO `News` (`News_id`, `Club_id`, `Title`, `Content`, `Publish_date`) VALUES
(1, 1, 'Hansi Flick: \'One more point, one game less\'', 'Hansi Flick was in philosophical mood  in his post game press appearance following his side\'s 1-1 draw at home to Real Betis in La Liga on Saturday. \"It\'s part of sport, we can\'t win every single game, said the blaugrana coach, before putting a positive spin on not taking all three points: \"I am happy with the point because it\'s one more. I am happy with the second half - we gave everything and we tried everything we could.\" \r\n\r\nFocus inwards :\r\nReflecting on the number of matches his team have played already this season, Flick added: \"We managed it well, the team is fine and I believe in that. We are proud of them.\" Thinking about Wednesday\'s Champions League game against Borussia Dortmund, Flick continued: \"We have an extra day to recover for the next game and that\'s good for the players. We have to look after them.\" \r\n\r\nFlick on Gavi : \r\nMidfielder Gavi marked his 100th league appearance with a goal and a hug in celebration with the German coach. \"He\'s getting better and better. He\'s getting back to top form and I think we can help him get there,\" concluded Flick. ', '2025-04-06'),
(2, 1, 'Barcelona extend lead but miss golden opportunity to surge ahead in La Liga', 'Although it was another point earned for Hansi Flick’s side against Real Betis, and as a result they stretch their lead at the top of the table ever so slightly, one can’t help but feel that this was two points dropped rather then one gained.\r\n\r\nOnce Gavi had given the hosts the lead, Barca could and should’ve eased into their normal patterns of play and, for large parts they did.\r\n\r\nWhere they were lacking was in attack with Lamine Yamal looking particularly tired on the night. His passes were often too casual or mis-timed and hints at a gruelling season for club and country eventually catching up with him in the way that it did with Pedri.\r\n\r\nCredit has to be given to Betis, however, who remained a threat after they’d equalised to silence the Estadio Lluis Companys.\r\n\r\nWith Real Madrid having surprisingly lost to Valencia for the first time in 17 years at the Santiago Bernabeu earlier in the day, the chance was there for Barca to surge six points clear in the title race.\r\n\r\nDespite setting the tone for virtually the whole game, the Blaugranes weren’t able to add any further goals, meaning that their advantage over Los Blancos isn’t as favourable as it could’ve been.\r\n\r\nIf there was one note of criticism to sound, it would be in Ronald Araujo’s direction. For a player that prides himself on the art of defence, why was he so passive in the move leading to the goal?\r\n\r\nHe should be embarrassed by just having stood there and allowing Natan to climb all over him.', '2025-04-06'),
(3, 2, 'Real Madrid 1-2 Valencia Match Report, La Liga: Vinicius Misses Another Penalty In Shock RMA Loss', '\r\nVinicius JuniorVinicius Junior reacts to his unsuccessful penalty\r\nVinicius Junior missed his second penalty in less than a month as Spanish champions Real Madrid lost 2-1 at home to Valencia, giving up valuable ground in La Liga\'s title race. (Highlights | More Football News)\r\n\r\nNow with a game in hand, Hansi Flick\'s Barcelona are top on 66 points, three clear of Madrid in second and nine ahead of third-placed Atletico.\r\n\r\nIf Barca win against Real Betis later on Saturday, they will extend their lead to six points with eight games to go.\r\n\r\nMadrid dominated possession throughout the game but wasted too many chances, with Valencia goalkeeper Giorgi Mamardashvili putting in a brilliant performance between the posts, including making a fine save to keep out Vinicius\' first-half penalty.\r\n\r\nVinicius, who also missed a spot-kick when Madrid eliminated Atletico in the Champions League\'s last 16, had a weak kick blocked by Mamardashvili\'s legs after Cesar Tarrega fouled Kylian Mbappe in the ninth minute.\r\n\r\n', '2025-04-06');

-- --------------------------------------------------------

--
-- Table structure for table `Player`
--

CREATE TABLE `Player` (
  `Player_id` int(11) NOT NULL,
  `Player_name` varchar(100) DEFAULT NULL,
  `Date_of_Birth` date DEFAULT NULL,
  `player_contact_info` varchar(100) DEFAULT NULL,
  `Sports_experience` text DEFAULT NULL,
  `Photo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Player`
--

INSERT INTO `Player` (`Player_id`, `Player_name`, `Date_of_Birth`, `player_contact_info`, `Sports_experience`, `Photo`) VALUES
(1, 'Lionel Messi', '1987-10-14', 'messi@gmail.com', '20', 'lionel-messi-celebrates-after-their-sides-third-goal-by-news-photo-1686170172.avif'),
(3, 'Vini', '2001-11-11', 'vini@gmail.com', '12', 'vinihead-1-480x480-1.png'),
(4, 'Antony', '1995-07-10', 'antony@gmail.com', '11', 'antony-manchester-united-2024-2025-1050362410h-1727778751-149994.jpg'),
(6, 'Luka Modrić', '1985-09-11', 'luka.modric@gmail.com', '20', 'luka.jpeg'),
(7, 'Erling Haaland', '2000-07-21', 'haaland@gmail.com', '8', 'erling-haland-reminding-arsenal-fans-that-they-have-golden-v0-5eob71ncwrge1.webp'),
(8, 'Cristiano Ronaldo', '1985-02-02', 'cr7@gmail.com', '20', 'Cristiano_Ronaldo_playing_for_Al_Nassr_FC_against_Persepolis,_September_2023_(cropped).jpg'),
(9, 'Lamine Yamal', '2007-12-07', 'lamine@gmail.com', '5', 'lamine.jpeg'),
(10, 'Robert Lewandowski', '1988-08-21', 'lewandowski@gmail.com', '18', 'robart lewandowski.jpeg'),
(11, 'Raphinha', '1996-12-14', 'raphinha@gmail.com', '8', 'raphinha-ballon-dor-case-scaled.jpg'),
(12, 'Kevin De Bruyne', '1991-06-28', 'kevin@gmail.com', '16', 'kevin-de-bruyne.webp'),
(13, 'Rodrigo Hernández Cascante', '1996-06-22', 'rodri@gmail.com', '10', 'rodrigo.webp'),
(14, 'BRUNO FERNANDES', '1994-10-08', 'bruno@gmail.com', '12', '1593.vresize.350.350.medium.99.webp');

-- --------------------------------------------------------

--
-- Table structure for table `Player_Performance`
--

CREATE TABLE `Player_Performance` (
  `Performance_id` int(11) NOT NULL,
  `Match_id` int(11) DEFAULT NULL,
  `Player_id` int(11) DEFAULT NULL,
  `goal_scored` int(11) DEFAULT NULL,
  `assists` int(11) DEFAULT NULL,
  `other_metrics` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Result`
--

CREATE TABLE `Result` (
  `Result_id` int(11) NOT NULL,
  `Match_id` int(11) DEFAULT NULL,
  `team1_score` int(11) DEFAULT NULL,
  `team2_score` int(11) DEFAULT NULL,
  `winner_team_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Result`
--

INSERT INTO `Result` (`Result_id`, `Match_id`, `team1_score`, `team2_score`, `winner_team_id`) VALUES
(2, 2, 4, 1, 5),
(3, 3, 1, 2, 7);

-- --------------------------------------------------------

--
-- Table structure for table `Team`
--

CREATE TABLE `Team` (
  `Team_id` int(11) NOT NULL,
  `Club_id` int(11) DEFAULT NULL,
  `Team_name` varchar(100) DEFAULT NULL,
  `Captain_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Team`
--

INSERT INTO `Team` (`Team_id`, `Club_id`, `Team_name`, `Captain_id`) VALUES
(4, 4, 'MANC', 13),
(5, 1, 'FCB', 11),
(6, 3, 'MANU', 14),
(7, 2, 'RMA', 6);

-- --------------------------------------------------------

--
-- Table structure for table `Team_Player`
--

CREATE TABLE `Team_Player` (
  `Team_id` int(11) NOT NULL,
  `Player_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Team_Player`
--

INSERT INTO `Team_Player` (`Team_id`, `Player_id`) VALUES
(4, 7),
(4, 12),
(4, 13),
(5, 9),
(5, 10),
(5, 11),
(6, 4),
(6, 14),
(7, 3),
(7, 6),
(7, 8);

-- --------------------------------------------------------

--
-- Table structure for table `Team_Tournament`
--

CREATE TABLE `Team_Tournament` (
  `Team_id` int(11) NOT NULL,
  `Tournament_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Team_Tournament`
--

INSERT INTO `Team_Tournament` (`Team_id`, `Tournament_id`) VALUES
(4, 3),
(5, 3),
(6, 3),
(7, 3);

-- --------------------------------------------------------

--
-- Table structure for table `Tournament`
--

CREATE TABLE `Tournament` (
  `Tournament_id` int(11) NOT NULL,
  `Tournament_name` varchar(100) DEFAULT NULL,
  `sport` varchar(50) DEFAULT NULL,
  `format` varchar(50) DEFAULT NULL,
  `Tournament_start_date` date DEFAULT NULL,
  `Tournament_end_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Tournament`
--

INSERT INTO `Tournament` (`Tournament_id`, `Tournament_name`, `sport`, `format`, `Tournament_start_date`, `Tournament_end_date`) VALUES
(3, 'UEFA Champions League', 'Football', 'Knockout', '2025-04-01', '2025-04-30');

-- --------------------------------------------------------

--
-- Table structure for table `Venue`
--

CREATE TABLE `Venue` (
  `Venue_id` int(11) NOT NULL,
  `Venue_name` varchar(100) DEFAULT NULL,
  `Venue_address` varchar(255) DEFAULT NULL,
  `Venue_contact_info` varchar(100) DEFAULT NULL,
  `Available_facilities` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Venue`
--

INSERT INTO `Venue` (`Venue_id`, `Venue_name`, `Venue_address`, `Venue_contact_info`, `Available_facilities`) VALUES
(1, 'Basundhara Football Stadium', 'Basundhara Residential Area, Dhaka, Bangladesh', 'basundharastadium@email.com', 'Floodlights, VIP Lounges, Press Box, First Aid Station, Restrooms, Parking, Food and Beverage, Security');

-- --------------------------------------------------------

--
-- Table structure for table `Venue_Booking`
--

CREATE TABLE `Venue_Booking` (
  `Booking_id` int(11) NOT NULL,
  `Venue_id` int(11) DEFAULT NULL,
  `Match_id` int(11) DEFAULT NULL,
  `Booking_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Venue_Booking`
--

INSERT INTO `Venue_Booking` (`Booking_id`, `Venue_id`, `Match_id`, `Booking_date`) VALUES
(4, 1, 2, '2025-04-01'),
(5, 1, 3, '2025-04-08');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Club`
--
ALTER TABLE `Club`
  ADD PRIMARY KEY (`Club_id`);

--
-- Indexes for table `Club_member`
--
ALTER TABLE `Club_member`
  ADD KEY `Club_id` (`Club_id`),
  ADD KEY `Member_id` (`Member_id`);

--
-- Indexes for table `Finance`
--
ALTER TABLE `Finance`
  ADD PRIMARY KEY (`Finance_id`),
  ADD KEY `Club_id` (`Club_id`);

--
-- Indexes for table `Match_`
--
ALTER TABLE `Match_`
  ADD PRIMARY KEY (`Match_id`),
  ADD KEY `Tournament_id` (`Tournament_id`),
  ADD KEY `Team1_id` (`Team1_id`),
  ADD KEY `Team2_id` (`Team2_id`);

--
-- Indexes for table `Member`
--
ALTER TABLE `Member`
  ADD PRIMARY KEY (`Member_id`);

--
-- Indexes for table `Message`
--
ALTER TABLE `Message`
  ADD PRIMARY KEY (`Message_id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `News`
--
ALTER TABLE `News`
  ADD PRIMARY KEY (`News_id`),
  ADD KEY `Club_id` (`Club_id`);

--
-- Indexes for table `Player`
--
ALTER TABLE `Player`
  ADD PRIMARY KEY (`Player_id`);

--
-- Indexes for table `Player_Performance`
--
ALTER TABLE `Player_Performance`
  ADD PRIMARY KEY (`Performance_id`),
  ADD KEY `Match_id` (`Match_id`),
  ADD KEY `Player_id` (`Player_id`);

--
-- Indexes for table `Result`
--
ALTER TABLE `Result`
  ADD PRIMARY KEY (`Result_id`),
  ADD KEY `Match_id` (`Match_id`),
  ADD KEY `winner_team_id` (`winner_team_id`);

--
-- Indexes for table `Team`
--
ALTER TABLE `Team`
  ADD PRIMARY KEY (`Team_id`),
  ADD KEY `Club_id` (`Club_id`),
  ADD KEY `Captain_id` (`Captain_id`);

--
-- Indexes for table `Team_Player`
--
ALTER TABLE `Team_Player`
  ADD PRIMARY KEY (`Team_id`,`Player_id`),
  ADD UNIQUE KEY `Player_id` (`Player_id`);

--
-- Indexes for table `Team_Tournament`
--
ALTER TABLE `Team_Tournament`
  ADD PRIMARY KEY (`Team_id`,`Tournament_id`),
  ADD KEY `Tournament_id` (`Tournament_id`);

--
-- Indexes for table `Tournament`
--
ALTER TABLE `Tournament`
  ADD PRIMARY KEY (`Tournament_id`);

--
-- Indexes for table `Venue`
--
ALTER TABLE `Venue`
  ADD PRIMARY KEY (`Venue_id`);

--
-- Indexes for table `Venue_Booking`
--
ALTER TABLE `Venue_Booking`
  ADD PRIMARY KEY (`Booking_id`),
  ADD KEY `Venue_id` (`Venue_id`),
  ADD KEY `Match_id` (`Match_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Club`
--
ALTER TABLE `Club`
  MODIFY `Club_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `Finance`
--
ALTER TABLE `Finance`
  MODIFY `Finance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `Match_`
--
ALTER TABLE `Match_`
  MODIFY `Match_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `Member`
--
ALTER TABLE `Member`
  MODIFY `Member_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `Message`
--
ALTER TABLE `Message`
  MODIFY `Message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `News`
--
ALTER TABLE `News`
  MODIFY `News_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `Player`
--
ALTER TABLE `Player`
  MODIFY `Player_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `Player_Performance`
--
ALTER TABLE `Player_Performance`
  MODIFY `Performance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `Result`
--
ALTER TABLE `Result`
  MODIFY `Result_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `Team`
--
ALTER TABLE `Team`
  MODIFY `Team_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `Tournament`
--
ALTER TABLE `Tournament`
  MODIFY `Tournament_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `Venue`
--
ALTER TABLE `Venue`
  MODIFY `Venue_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `Venue_Booking`
--
ALTER TABLE `Venue_Booking`
  MODIFY `Booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Club_member`
--
ALTER TABLE `Club_member`
  ADD CONSTRAINT `club_member_ibfk_1` FOREIGN KEY (`Club_id`) REFERENCES `Club` (`Club_id`),
  ADD CONSTRAINT `club_member_ibfk_2` FOREIGN KEY (`Member_id`) REFERENCES `Member` (`Member_id`);

--
-- Constraints for table `Finance`
--
ALTER TABLE `Finance`
  ADD CONSTRAINT `finance_ibfk_1` FOREIGN KEY (`Club_id`) REFERENCES `Club` (`Club_id`);

--
-- Constraints for table `Match_`
--
ALTER TABLE `Match_`
  ADD CONSTRAINT `match__ibfk_1` FOREIGN KEY (`Tournament_id`) REFERENCES `Tournament` (`Tournament_id`),
  ADD CONSTRAINT `match__ibfk_2` FOREIGN KEY (`Team1_id`) REFERENCES `Team` (`Team_id`),
  ADD CONSTRAINT `match__ibfk_3` FOREIGN KEY (`Team2_id`) REFERENCES `Team` (`Team_id`);

--
-- Constraints for table `Message`
--
ALTER TABLE `Message`
  ADD CONSTRAINT `message_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `Member` (`Member_id`),
  ADD CONSTRAINT `message_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `Member` (`Member_id`);

--
-- Constraints for table `News`
--
ALTER TABLE `News`
  ADD CONSTRAINT `news_ibfk_1` FOREIGN KEY (`Club_id`) REFERENCES `Club` (`Club_id`);

--
-- Constraints for table `Player_Performance`
--
ALTER TABLE `Player_Performance`
  ADD CONSTRAINT `player_performance_ibfk_1` FOREIGN KEY (`Match_id`) REFERENCES `Match_` (`Match_id`),
  ADD CONSTRAINT `player_performance_ibfk_2` FOREIGN KEY (`Player_id`) REFERENCES `Player` (`Player_id`);

--
-- Constraints for table `Result`
--
ALTER TABLE `Result`
  ADD CONSTRAINT `result_ibfk_1` FOREIGN KEY (`Match_id`) REFERENCES `Match_` (`Match_id`),
  ADD CONSTRAINT `result_ibfk_2` FOREIGN KEY (`winner_team_id`) REFERENCES `Team` (`Team_id`);

--
-- Constraints for table `Team`
--
ALTER TABLE `Team`
  ADD CONSTRAINT `team_ibfk_1` FOREIGN KEY (`Club_id`) REFERENCES `Club` (`Club_id`),
  ADD CONSTRAINT `team_ibfk_2` FOREIGN KEY (`Captain_id`) REFERENCES `Player` (`Player_id`);

--
-- Constraints for table `Team_Player`
--
ALTER TABLE `Team_Player`
  ADD CONSTRAINT `team_player_ibfk_1` FOREIGN KEY (`Team_id`) REFERENCES `Team` (`Team_id`),
  ADD CONSTRAINT `team_player_ibfk_2` FOREIGN KEY (`Player_id`) REFERENCES `Player` (`Player_id`);

--
-- Constraints for table `Team_Tournament`
--
ALTER TABLE `Team_Tournament`
  ADD CONSTRAINT `team_tournament_ibfk_1` FOREIGN KEY (`Team_id`) REFERENCES `Team` (`Team_id`),
  ADD CONSTRAINT `team_tournament_ibfk_2` FOREIGN KEY (`Tournament_id`) REFERENCES `Tournament` (`Tournament_id`);

--
-- Constraints for table `Venue_Booking`
--
ALTER TABLE `Venue_Booking`
  ADD CONSTRAINT `venue_booking_ibfk_1` FOREIGN KEY (`Venue_id`) REFERENCES `Venue` (`Venue_id`),
  ADD CONSTRAINT `venue_booking_ibfk_2` FOREIGN KEY (`Match_id`) REFERENCES `Match_` (`Match_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
