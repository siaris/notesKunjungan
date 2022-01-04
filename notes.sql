CREATE TABLE IF NOT EXISTS `mr_notes` (
  `detail_reg` int(11) NOT NULL,
  `notes_json` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `mr_notes`
 ADD PRIMARY KEY (`detail_reg`), ADD UNIQUE KEY `detail_reg` (`detail_reg`);
