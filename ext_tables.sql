CREATE TABLE tx_formlog_domain_model_logentry (
    form_identifier VARCHAR(255),
    form_data TEXT,
    finisher_data TEXT,
);

CREATE TABLE tx_formlog_domain_model_configuration (
    form_identifier VARCHAR(255),
    header_elements VARCHAR(255),
    finisher_options VARCHAR(255),
    items_per_page INT(11) unsigned DEFAULT '0' NOT NULL,
);
