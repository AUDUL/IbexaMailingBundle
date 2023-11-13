<?php

declare(strict_types=1);

namespace IbexaMailingBundle;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231113102632 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("create table mailing_campaign
(
    CAMP_id                bigint auto_increment
        primary key,
    CAMP_sender_name       varchar(255) not null,
    CAMP_sender_email      varchar(255) not null,
    CAMP_report_email      varchar(255) not null,
    CAMP_return_path_email varchar(255) not null,
    CAMP_siteaccess_limit  longtext     null comment '(DC2Type:array)',
    OBJ_created            datetime     not null,
    OBJ_updated            datetime     not null,
    OBJ_names              longtext     not null comment '(DC2Type:array)',
    EZ_locationId          int          null
)
    collate = utf8mb4_unicode_ci;");

        $this->addSql("create table mailing_confirmation_token
(
    CT_id       char(36) not null comment '(DC2Type:guid)'
        primary key,
    CT_payload  longtext not null comment '(DC2Type:array)',
    OBJ_created datetime not null,
    OBJ_updated datetime not null
)
    collate = utf8mb4_unicode_ci;");

        $this->addSql("create table mailing_mailing
(
    MAIL_id             bigint auto_increment
        primary key,
    MAIL_status         varchar(255) not null,
    MAIL_recurring      tinyint(1)   not null,
    MAIL_hours_of_day   longtext     not null comment '(DC2Type:array)',
    MAIL_days_of_week   longtext     null comment '(DC2Type:array)',
    MAIL_days_of_month  longtext     null comment '(DC2Type:array)',
    MAIL_days_of_year   longtext     null comment '(DC2Type:array)',
    MAIL_weeks_of_month longtext     null comment '(DC2Type:array)',
    MAIL_months_of_year longtext     null comment '(DC2Type:array)',
    MAIL_weeks_of_year  longtext     null comment '(DC2Type:array)',
    MAIL_subject        varchar(255) not null,
    MAIL_siteaccess     varchar(255) not null,
    OBJ_created         datetime     not null,
    OBJ_updated         datetime     not null,
    OBJ_names           longtext     not null comment '(DC2Type:array)',
    EZ_locationId       int          null,
    CAMP_id             bigint       null,
    constraint FK_2F09D431DEE5E62B
        foreign key (CAMP_id) references mailing_campaign (CAMP_id)
)
    collate = utf8mb4_unicode_ci;");


        $this->addSql("create table mailing_broadcast
(
    BDCST_id               bigint auto_increment
        primary key,
    BDCST_started          datetime not null,
    BDCST_ended            datetime null,
    BDCST_email_sent_count int      not null,
    BDCST_html             longtext not null,
    OBJ_created            datetime not null,
    OBJ_updated            datetime not null,
    MAIL_id                bigint   null,
    constraint FK_A9F40CBA6195D391
        foreign key (MAIL_id) references mailing_mailing (MAIL_id)
)
    collate = utf8mb4_unicode_ci;");

        $this->addSql("create index IDX_A9F40CBA6195D391
    on mailing_broadcast (MAIL_id);");

        $this->addSql("create index IDX_2F09D431DEE5E62B
    on mailing_mailing (CAMP_id);");

        $this->addSql("create table mailing_mailing_list
(
    ML_id                   bigint auto_increment
        primary key,
    ML_siteaccess_limit     longtext     null comment '(DC2Type:array)',
    ML_approbation          tinyint(1)   not null,
    OBJ_remote_id           varchar(255) null,
    OBJ_remote_last_synchro datetime     null,
    OBJ_remote_status       smallint     null,
    OBJ_created             datetime     not null,
    OBJ_updated             datetime     not null,
    OBJ_names               longtext     not null comment '(DC2Type:array)'
)
    collate = utf8mb4_unicode_ci;");

        $this->addSql("create table mailing_campaign_mailinglists_destination
(
    ML_id   bigint not null,
    CAMP_id bigint not null,
    primary key (ML_id, CAMP_id),
    constraint FK_3FEF05962E839042
        foreign key (ML_id) references mailing_campaign (CAMP_id),
    constraint FK_3FEF0596DEE5E62B
        foreign key (CAMP_id) references mailing_mailing_list (ML_id)
)
    collate = utf8mb4_unicode_ci;");

        $this->addSql("create index IDX_3FEF05962E839042
    on mailing_campaign_mailinglists_destination (ML_id);");

        $this->addSql("create index IDX_3FEF0596DEE5E62B
    on mailing_campaign_mailinglists_destination (CAMP_id);");

        $this->addSql("create table mailing_stats_hit
(
    STHIT_id           bigint auto_increment
        primary key,
    STHIT_url          varchar(255) not null,
    STHIT_user_key     varchar(255) not null,
    STHIT_os_name      varchar(255) null,
    STHIT_browser_name varchar(255) null,
    OBJ_created        datetime     not null,
    OBJ_updated        datetime     not null,
    BDCST_id           bigint       null,
    constraint FK_C576A595CE029D
        foreign key (BDCST_id) references mailing_broadcast (BDCST_id)
)
    collate = utf8mb4_unicode_ci;");

        $this->addSql("create index IDX_C576A595CE029D
    on mailing_stats_hit (BDCST_id);");

        $this->addSql("create table mailing_user
(
    USER_id                 bigint auto_increment
        primary key,
    USER_email              varchar(255) not null,
    USER_first_name         varchar(255) null,
    USER_last_name          varchar(255) null,
    USER_gender             varchar(255) null,
    USER_birth_date         date         null,
    USER_phone              varchar(255) null,
    USER_zipcode            varchar(255) null,
    USER_city               varchar(255) null,
    USER_state              varchar(255) null,
    USER_country            varchar(255) null,
    USER_job_title          varchar(255) null,
    USER_company            varchar(255) null,
    USER_origin             varchar(255) not null,
    USER_status             varchar(255) not null,
    USER_restricted         tinyint(1)   not null,
    OBJ_created             datetime     not null,
    OBJ_updated             datetime     not null,
    OBJ_remote_id           varchar(255) null,
    OBJ_remote_last_synchro datetime     null,
    OBJ_remote_status       smallint     null,
    constraint unique_email
        unique (USER_email)
)
    collate = utf8mb4_unicode_ci;");

        $this->addSql("create table mailing_registrations
(
    REG_id       bigint auto_increment
        primary key,
    REG_approved tinyint(1) not null,
    OBJ_created  datetime   not null,
    OBJ_updated  datetime   not null,
    ML_id        bigint     not null,
    USER_id      bigint     not null,
    constraint unique_registration
        unique (ML_id, USER_id),
    constraint FK_71F8D4192E839042
        foreign key (ML_id) references mailing_mailing_list (ML_id),
    constraint FK_71F8D419E8C6F05
        foreign key (USER_id) references mailing_user (USER_id)
)     collate = utf8mb4_unicode_ci;");

        $this->addSql("create index IDX_71F8D4192E839042
    on mailing_registrations (ML_id);");

        $this->addSql("create index IDX_71F8D419E8C6F05
    on mailing_registrations (USER_id);");

        $this->addSql("create index search_idx_approved
    on mailing_registrations (REG_approved);");

        $this->addSql("create index search_idx_restricted
    on mailing_user (USER_restricted);");

        $this->addSql("create index search_idx_status
    on mailing_user (USER_status);");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("drop table mailing_broadcast;");
        $this->addSql("drop table mailing_campaign;");
        $this->addSql("drop table mailing_campaign_mailinglists_destination;");
        $this->addSql("drop table mailing_confirmation_token;");
        $this->addSql("drop table mailing_mailing;");
        $this->addSql("drop table mailing_mailing_list;");
        $this->addSql("drop table mailing_registrations;");
        $this->addSql("drop table mailing_stats_hit;");
        $this->addSql("drop table mailing_user;");
    }
}
