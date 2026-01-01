       Dcl-Pi *n ;
         Library Char(10);
       End-Pi;

       Dcl-S CreateLibrary      Char(300);
       Dcl-S CreateProcedure    Char(300);
       Dcl-S CreateTable        Char(300);
       Dcl-S CreateIndex        Char(300);
       Dcl-S CreateUserTable    Char(300);
       Dcl-S CreateRefreshTable Char(300);
       Dcl-S CompileRPG         Char(300);

       Exec Sql
         Set Option Closqlcsr = *Endmod , Commit = *None;

       CreateLibrary = 'CRTLIB LIB(' + %Trim(Library) + ')';

       Exec Sql
         CALL QSYS2.QCMDEXC(:CreateLibrary);

        CreateUserTable =
          'CREATE OR REPLACE TABLE ' + %Trim(Library) + '.USER_LOGINS ( ' +
             'USER_ID INT(10) NOT NULL, '                      +
             'USERNAME VARCHAR(20) PRIMARY KEY, '              +
             'PWORD VARCHAR(72) NOT NULL, '                    +
             'FIRST_NAME VARCHAR(50), '                        +
             'LAST_NAME VARCHAR(50), '                         +
             'CREATED_AT TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, ' +
             'UPDATED_AT TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP)';

        Exec Sql
          Execute Immediate :CreateUserTable;

        CreateTable =
          'CREATE OR REPLACE TABLE ' + %Trim(Library) + '.BALANCETAB ( ' +
             'USERNAME VARCHAR(20) NOT NULL, '                +
             'TRANSTYPE CHAR(1) NOT NULL, '                   +
             'AMOUNT DECIMAL(15, 2) NOT NULL, '               +
             'TRANSTIME TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP)';

        Exec Sql
          Execute Immediate :CreateTable;

        CreateIndex =
          'CREATE OR REPLACE INDEX ' + %Trim(Library)   +
          '.BALANCELOG ON ' + %Trim(Library) +
          '.BALANCETAB (USER)';

        Exec Sql
          Execute Immediate :CreateIndex;

        CreateRefreshTable =
        'CREATE OR REPLACE TABLE ' + %Trim(Library) + '.REFRESH_TOKENS ( ' +
               'TOKEN_HASH VARCHAR(128) PRIMARY KEY, ' +
               'USERNAME VARCHAR(255) NOT NULL, '      +
               'EXPIRES_AT BIGINT NOT NULL, '          +
               'CREATED_AT BIGINT NOT NULL '           +
               ')';

        Exec Sql
          Execute Immediate :CreateRefreshTable;

        CreateProcedure =
       'CREATE OR REPLACE PROCEDURE '+ %Trim(Library) +'.PROCESSTRANSACTION ( '+
              'IN AMOUNT DECIMAL(15, 2), '   +
              'IN TTYPE CHAR(1), '           +
              'IN USERNAME VARCHAR(20), '    +
              'OUT BALANCE DECIMAL(15, 2), ' +
              'OUT MESSAGE CHAR(35)) '       +
           'LANGUAGE RPGLE '                 +
           'MODIFIES SQL DATA '              +
           'EXTERNAL NAME ''JANDERSON1/TRANSACT'' ' +
           'PARAMETER STYLE GENERAL';

        Exec Sql
          Execute Immediate :CreateProcedure;

        CompileRPG = 'CRTBNDRPG PGM(' + %Trim(Library) + '/TRANSACT) ' +
                     'SRCFILE(' + %Trim(Library) + '/QRPGLESRC) '      +
                     'DBGVIEW(*SOURCE)';

        Exec Sql
          CALL QSYS2.QCMDEXC(:CompileRPG);

        *INLR = *ON;
        Return;