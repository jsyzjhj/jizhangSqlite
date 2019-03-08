CREATE TABLE jizhang_account_class ( 
    classid   INTEGER        PRIMARY KEY AUTOINCREMENT,
    classname VARCHAR( 20 )  NOT NULL,
    classtype INT( 1 )       NOT NULL,
    ufid      INT( 8 )       NOT NULL 
);
CREATE TABLE jizhang_bank ( 
    bankid       INTEGER           PRIMARY KEY AUTOINCREMENT,
    bankname     VARCHAR( 50 )     NOT NULL,
    bankaccount  VARCHAR( 50 )     NOT NULL,
    balancemoney DECIMAL( 10, 2 )  NOT NULL,
    addtime      INT( 11 )         NOT NULL,
    updatetime   INT( 11 )         NOT NULL,
    userid       INT( 8 )          NOT NULL 
);
CREATE TABLE jizhang_user ( 
    uid      INTEGER        PRIMARY KEY AUTOINCREMENT,
    username VARCHAR( 15 )  NOT NULL,
    password VARCHAR( 35 )  NOT NULL,
    email    VARCHAR( 20 )  NOT NULL,
    Isallow  SMALLINT( 2 )  NOT NULL
                            DEFAULT ( 0 ),
    Isadmin  SMALLINT( 2 )  DEFAULT ( 0 ),
    addtime  INT( 11 )      NOT NULL,
    utime    INT( 11 )      NOT NULL,
    salt     VARCHAR( 35 )  NOT NULL 
);
CREATE TABLE jizhang_account ( 
    acid      INTEGER        PRIMARY KEY AUTOINCREMENT,
    acmoney   INTEGER( 10 )  NOT NULL,
    acclassid INT( 8 )       NOT NULL,
    actime    DATETIME       NOT NULL,
    acremark  VARCHAR( 50 ),
    jiid      INT( 8 )       NOT NULL,
    zhifu     INT( 8 )       NOT NULL,
    bankid    INT( 8 )       DEFAULT ( 0 ) 
);
CREATE INDEX idx_jizhang_account_class ON jizhang_account_class ( 
    classid 
);
CREATE INDEX idx_jizhang_account ON jizhang_account ( 
    acid 
);
CREATE INDEX idx_jizhang_account_time ON jizhang_account ( 
    actime 
);

