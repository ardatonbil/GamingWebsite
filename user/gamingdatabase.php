<?php
/**
 * Export to PHP Array plugin for PHPMyAdmin
 * @version 5.2.1
 */

/**
 * Database `gamingdatabase`
 */

/* `gamingdatabase`.`cpu` */
$cpu = array(
  array('CPUID' => '1','RequirementID' => '1','Model' => 'Core 2 Duo','Manufacturer' => 'Intel','Cores' => '2','ClockFrequency' => '2'),
  array('CPUID' => '2','RequirementID' => '2','Model' => 'Core i3-3225','Manufacturer' => 'Intel','Cores' => '2','ClockFrequency' => '3.3'),
  array('CPUID' => '3','RequirementID' => '3','Model' => 'Core i7-12700','Manufacturer' => 'Intel','Cores' => '12','ClockFrequency' => '2.1'),
  array('CPUID' => '4','RequirementID' => '4','Model' => 'Core i5-2500K','Manufacturer' => 'Intel','Cores' => '4','ClockFrequency' => '3.3'),
  array('CPUID' => '5','RequirementID' => '5','Model' => 'Core i3-3210','Manufacturer' => 'Intel','Cores' => '2','ClockFrequency' => '3.2'),
  array('CPUID' => '6','RequirementID' => '6','Model' => 'Core i5-6600k','Manufacturer' => 'Intel','Cores' => '4','ClockFrequency' => '3.5'),
  array('CPUID' => '7','RequirementID' => '7','Model' => 'Core 2 Quad Q6600','Manufacturer' => 'Intel','Cores' => '4','ClockFrequency' => '2.4'),
  array('CPUID' => '8','RequirementID' => '8','Model' => 'Intel i3-2100','Manufacturer' => 'Intel','Cores' => '2','ClockFrequency' => '3.1'),
  array('CPUID' => '9','RequirementID' => '9','Model' => 'Core i5-2300','Manufacturer' => 'Intel','Cores' => '4','ClockFrequency' => '2.8'),
  array('CPUID' => '10','RequirementID' => '10','Model' => 'Intel i5-4460','Manufacturer' => 'Intel','Cores' => '4','ClockFrequency' => '3.2')
);

/* `gamingdatabase`.`game` */
$game = array(
  array('GameID' => 'G001','Title' => 'The Elder Scrolls V: Skyrim','ReleaseYear' => '2011','Genre' => 'RPG','Platform' => 'PC','InGameTransaction' => '0','Multiplayer' => '0'),
  array('GameID' => 'G002','Title' => 'Fortnite','ReleaseYear' => '2017','Genre' => 'Battle Royale','Platform' => 'Multiple','InGameTransaction' => '1','Multiplayer' => '1'),
  array('GameID' => 'G003','Title' => 'Cyberpunk 2077','ReleaseYear' => '2020','Genre' => 'RPG','Platform' => 'PC','InGameTransaction' => '1','Multiplayer' => '0'),
  array('GameID' => 'G004','Title' => 'Red Dead Redemption 2','ReleaseYear' => '2018','Genre' => 'Action-Adventure','Platform' => 'Multiple','InGameTransaction' => '1','Multiplayer' => '1'),
  array('GameID' => 'G005','Title' => 'Minecraft','ReleaseYear' => '2011','Genre' => 'Sandbox','Platform' => 'Multiple','InGameTransaction' => '1','Multiplayer' => '1'),
  array('GameID' => 'G006','Title' => 'FIFA 23','ReleaseYear' => '2022','Genre' => 'Sports','Platform' => 'Multiple','InGameTransaction' => '1','Multiplayer' => '1'),
  array('GameID' => 'G007','Title' => 'Grand Theft Auto V','ReleaseYear' => '2013','Genre' => 'Action-Adventure','Platform' => 'Multiple','InGameTransaction' => '1','Multiplayer' => '1'),
  array('GameID' => 'G008','Title' => 'Overcooked 2','ReleaseYear' => '2018','Genre' => 'Simulation','Platform' => 'Multiple','InGameTransaction' => '0','Multiplayer' => '1'),
  array('GameID' => 'G009','Title' => 'Fallout 4','ReleaseYear' => '2015','Genre' => 'RPG','Platform' => 'Multiple','InGameTransaction' => '0','Multiplayer' => '0'),
  array('GameID' => 'G010','Title' => 'Forza Horizon 5','ReleaseYear' => '2021','Genre' => 'Racing','Platform' => 'Multiple','InGameTransaction' => '1','Multiplayer' => '1')
);

/* `gamingdatabase`.`game_publisher` */
$game_publisher = array(
  array('GameID' => 'G001','PublisherID' => 'P008'),
  array('GameID' => 'G002','PublisherID' => 'P010'),
  array('GameID' => 'G003','PublisherID' => 'P003'),
  array('GameID' => 'G004','PublisherID' => 'P004'),
  array('GameID' => 'G005','PublisherID' => 'P005'),
  array('GameID' => 'G006','PublisherID' => 'P006'),
  array('GameID' => 'G007','PublisherID' => 'P004'),
  array('GameID' => 'G008','PublisherID' => 'P007'),
  array('GameID' => 'G009','PublisherID' => 'P008'),
  array('GameID' => 'G010','PublisherID' => 'P009')
);

/* `gamingdatabase`.`gpu` */
$gpu = array(
  array('GPUID' => '1','RequirementID' => '1','Model' => 'GeForce 7600','Manufacturer' => 'NVIDIA','VRAMSize' => '512'),
  array('GPUID' => '2','RequirementID' => '2','Model' => 'Intel HD 4000','Manufacturer' => 'Intel','VRAMSize' => '1024'),
  array('GPUID' => '3','RequirementID' => '3','Model' => 'RTX 2060 Super','Manufacturer' => 'NVIDIA','VRAMSize' => '8192'),
  array('GPUID' => '4','RequirementID' => '4','Model' => 'GeForce GTX 770','Manufacturer' => 'NVIDIA','VRAMSize' => '2048'),
  array('GPUID' => '5','RequirementID' => '5','Model' => 'Intel HD Graphics 4000','Manufacturer' => 'Intel','VRAMSize' => '512'),
  array('GPUID' => '6','RequirementID' => '6','Model' => 'GeForce GTX 1050 Ti','Manufacturer' => 'NVIDIA','VRAMSize' => '4096'),
  array('GPUID' => '7','RequirementID' => '7','Model' => 'NVIDIA 9800 GT','Manufacturer' => 'NVIDIA','VRAMSize' => '1024'),
  array('GPUID' => '8','RequirementID' => '8','Model' => 'GeForce GT 630','Manufacturer' => 'NVIDIA','VRAMSize' => '1024'),
  array('GPUID' => '9','RequirementID' => '9','Model' => 'GTX 550 Ti','Manufacturer' => 'NVIDIA','VRAMSize' => '2048'),
  array('GPUID' => '10','RequirementID' => '10','Model' => 'NVIDIA GTX 970','Manufacturer' => 'NVIDIA','VRAMSize' => '4096')
);

/* `gamingdatabase`.`minimumrequirements` */
$minimumrequirements = array(
  array('RequirementID' => '1','GameID' => 'G001'),
  array('RequirementID' => '2','GameID' => 'G002'),
  array('RequirementID' => '3','GameID' => 'G003'),
  array('RequirementID' => '4','GameID' => 'G004'),
  array('RequirementID' => '5','GameID' => 'G005'),
  array('RequirementID' => '6','GameID' => 'G006'),
  array('RequirementID' => '7','GameID' => 'G007'),
  array('RequirementID' => '8','GameID' => 'G008'),
  array('RequirementID' => '9','GameID' => 'G009'),
  array('RequirementID' => '10','GameID' => 'G010')
);

/* `gamingdatabase`.`publisher` */
$publisher = array(
  array('PublisherID' => 'P001','PublisherName' => 'Ubisoft','MarketValue' => '1560000000.00','NumberOfGames' => '0','EstablishmentYear' => '1986'),
  array('PublisherID' => 'P002','PublisherName' => 'Square Enix','MarketValue' => '5660000000.00','NumberOfGames' => '0','EstablishmentYear' => '2003'),
  array('PublisherID' => 'P003','PublisherName' => 'CD Projekt Red','MarketValue' => '5000000000.00','NumberOfGames' => '1','EstablishmentYear' => '1994'),
  array('PublisherID' => 'P004','PublisherName' => 'Rockstar Games','MarketValue' => '25000000000.00','NumberOfGames' => '2','EstablishmentYear' => '1998'),
  array('PublisherID' => 'P005','PublisherName' => 'Mojang Studios','MarketValue' => '2000000000.00','NumberOfGames' => '1','EstablishmentYear' => '2009'),
  array('PublisherID' => 'P006','PublisherName' => 'Electronic Arts','MarketValue' => '38000000000.00','NumberOfGames' => '1','EstablishmentYear' => '1982'),
  array('PublisherID' => 'P007','PublisherName' => 'Team17','MarketValue' => '600000000.00','NumberOfGames' => '1','EstablishmentYear' => '1990'),
  array('PublisherID' => 'P008','PublisherName' => 'Bethesda Softworks','MarketValue' => '7500000000.00','NumberOfGames' => '2','EstablishmentYear' => '1986'),
  array('PublisherID' => 'P009','PublisherName' => 'Xbox Game Studios','MarketValue' => '35000000000.00','NumberOfGames' => '1','EstablishmentYear' => '2000'),
  array('PublisherID' => 'P010','PublisherName' => 'Epic Games','MarketValue' => '31500000000.00','NumberOfGames' => '1','EstablishmentYear' => '1991')
);

/* `gamingdatabase`.`ram` */
$ram = array(
  array('RAMID' => '1','RequirementID' => '1','Size' => '2','Type' => 'DDR2'),
  array('RAMID' => '2','RequirementID' => '2','Size' => '4','Type' => 'DDR3'),
  array('RAMID' => '3','RequirementID' => '3','Size' => '16','Type' => 'DDR4'),
  array('RAMID' => '4','RequirementID' => '4','Size' => '8','Type' => 'DDR3'),
  array('RAMID' => '5','RequirementID' => '5','Size' => '4','Type' => 'DDR3'),
  array('RAMID' => '6','RequirementID' => '6','Size' => '8','Type' => 'DDR4'),
  array('RAMID' => '7','RequirementID' => '7','Size' => '4','Type' => 'DDR3'),
  array('RAMID' => '8','RequirementID' => '8','Size' => '4','Type' => 'DDR3'),
  array('RAMID' => '9','RequirementID' => '9','Size' => '8','Type' => 'DDR3'),
  array('RAMID' => '10','RequirementID' => '10','Size' => '8','Type' => 'DDR4')
);

/* `gamingdatabase`.`restrictedby` */
$restrictedby = array(
  array('RestrictionID' => 'PEGI 18','GameID' => 'G001'),
  array('RestrictionID' => 'PEGI 12','GameID' => 'G002'),
  array('RestrictionID' => 'PEGI 18','GameID' => 'G003'),
  array('RestrictionID' => 'PEGI 18','GameID' => 'G004'),
  array('RestrictionID' => 'PEGI 7','GameID' => 'G005'),
  array('RestrictionID' => 'PEGI 7','GameID' => 'G006'),
  array('RestrictionID' => 'PEGI 18','GameID' => 'G007'),
  array('RestrictionID' => 'PEGI 7','GameID' => 'G008'),
  array('RestrictionID' => 'PEGI 18','GameID' => 'G009'),
  array('RestrictionID' => 'PEGI 7','GameID' => 'G010')
);

/* `gamingdatabase`.`restriction` */
$restriction = array(
  array('Category' => 'Teen','PEGI_ID' => 'PEGI 12'),
  array('Category' => 'Mature','PEGI_ID' => 'PEGI 18'),
  array('Category' => 'Everyone','PEGI_ID' => 'PEGI 7')
);

/* `gamingdatabase`.`storage` */
$storage = array(
  array('StorageID' => '1','RequirementID' => '1','Size' => '6','Type' => 'HDD'),
  array('StorageID' => '2','RequirementID' => '2','Size' => '16','Type' => 'HDD'),
  array('StorageID' => '3','RequirementID' => '3','Size' => '70','Type' => 'SSD'),
  array('StorageID' => '4','RequirementID' => '4','Size' => '150','Type' => 'HDD'),
  array('StorageID' => '5','RequirementID' => '5','Size' => '1','Type' => 'HDD'),
  array('StorageID' => '6','RequirementID' => '6','Size' => '100','Type' => 'HDD'),
  array('StorageID' => '7','RequirementID' => '7','Size' => '120','Type' => 'HDD'),
  array('StorageID' => '8','RequirementID' => '8','Size' => '3','Type' => 'HDD'),
  array('StorageID' => '9','RequirementID' => '9','Size' => '30','Type' => 'HDD'),
  array('StorageID' => '10','RequirementID' => '10','Size' => '110','Type' => 'SSD')
);
