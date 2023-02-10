#
# Table structure for table 'tx_clubdirectory_domain_model_club'
#
CREATE TABLE tx_clubdirectory_domain_model_club (
	title varchar(255) DEFAULT '' NOT NULL,
	path_segment varchar(2048) DEFAULT '' NOT NULL,
	activity text NOT NULL,
	contact_person varchar(255) DEFAULT '' NOT NULL,
	contact_times text NOT NULL,
	email varchar(255) DEFAULT '' NOT NULL,
	website varchar(255) DEFAULT '' NOT NULL,
	members varchar(255) DEFAULT '' NOT NULL,
	club_home varchar(255) DEFAULT '' NOT NULL,
	description text NOT NULL,
	fe_users int(11) DEFAULT '0' NOT NULL,
	logo int(11) DEFAULT '0' NOT NULL,
	images int(11) DEFAULT '0' NOT NULL,
	facebook varchar(255) DEFAULT '' NOT NULL,
	twitter varchar(255) DEFAULT '' NOT NULL,
	instagram varchar(255) DEFAULT '' NOT NULL,
	tags varchar(255) DEFAULT '' NOT NULL,
	district int(11) unsigned DEFAULT '0',
	addresses int(11) unsigned DEFAULT '0',
);

#
# Table structure for table 'tx_clubdirectory_domain_model_address'
#
CREATE TABLE tx_clubdirectory_domain_model_address (
	title varchar(255) DEFAULT '' NOT NULL,
	street varchar(255) DEFAULT '' NOT NULL,
	house_number varchar(255) DEFAULT '' NOT NULL,
	zip varchar(255) DEFAULT '' NOT NULL,
	city varchar(255) DEFAULT '' NOT NULL,
	telephone varchar(255) DEFAULT '' NOT NULL,
	fax varchar(255) DEFAULT '' NOT NULL,
	barrier_free varchar(255) DEFAULT '' NOT NULL,
	club int(11) unsigned DEFAULT '0',
);

#
# Table structure for table 'tx_clubdirectory_domain_model_district'
#
CREATE TABLE tx_clubdirectory_domain_model_district (
	district varchar(255) DEFAULT '' NOT NULL,
);

#
# Table structure for table 'tx_clubdirectory_club_user_mm'
#
CREATE TABLE tx_clubdirectory_club_user_mm (
	uid_local int(11) unsigned DEFAULT '0' NOT NULL,
	uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	sorting_foreign int(11) unsigned DEFAULT '0' NOT NULL,

	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);
