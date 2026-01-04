       Dcl-Pi *n ;
         Library Char(10);
       End-Pi;

       Dcl-S CreateLibrary      Char(500);
       Dcl-S CreateProcedure    Char(500);
       Dcl-S CreateTable        Char(500);
       Dcl-S CreateIndex        Char(500);
       Dcl-S CreateUserTable    Char(500);
       Dcl-S CreateRefreshTable Char(500);
       Dcl-S CompileRPG         Char(500);
       Dcl-S DropIndex          Char(500);

       Exec Sql
         Set Option Closqlcsr = *Endmod , Commit = *None;

       CreateLibrary = 'CRTLIB LIB(' + %Trim(Library) + ')';

       Exec Sql
         CALL QSYS2.QCMDEXC(:CreateLibrary);

        CreateUserTable =
          'CREATE OR REPLACE TABLE ' + %Trim(Library) + '.USER_LOGINS ( ' +
             'USER_ID INTEGER GENERATED ALWAYS AS IDENTITY, '  +
             'USERNAME VARCHAR(20) NOT NULL, '                 +
             'EMAIL VARCHAR(100) NOT NULL, '                   +
             'PWORD VARCHAR(72) NOT NULL, '                    +
             'FIRST_NAME VARCHAR(50) NOT NULL, '               +
             'LAST_NAME VARCHAR(50) NOT NULL, '                +
             'CREATED_AT TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, ' +
             'UPDATED_AT TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP)';

        Exec Sql
          Prepare #CreateUserTable From :CreateUserTable;

        Exec Sql
          Execute #CreateUserTable Using :CreateUserTable;

        CreateTable =
          'CREATE OR REPLACE TABLE ' + %Trim(Library) + '.BALANCETAB ( ' +
             'USERNAME VARCHAR(20) NOT NULL, '                +
             'TRANSTYPE CHAR(1) NOT NULL, '                   +
             'AMOUNT DECIMAL(15, 2) NOT NULL, '               +
             'TRANSTIME TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP) ' +
             'RCDFMT BAL';

        Exec Sql
          Prepare #CreateTable From :CreateTable;

        Exec Sql
          Execute #CreateTable Using :CreateTable;

        DropIndex =
          'DROP INDEX ' + %Trim(Library) + '.BALANCELOG';

        Exec Sql
          Prepare #DropIndex From :DropIndex;
        Exec Sql
          Execute #DropIndex Using :DropIndex;

        CreateIndex =
          'CREATE INDEX ' + %Trim(Library)   +
          '.BALANCELOG ON ' + %Trim(Library) +
          '.BALANCETAB (USERNAME)';

        Exec Sql
          Prepare #CreateIndex From :CreateIndex;

        Exec Sql
          Execute #CreateIndex Using :CreateIndex;

        CreateRefreshTable =
        'CREATE OR REPLACE TABLE ' + %Trim(Library) + '.REFRESH_TOKENS ( ' +
               'TOKEN_HASH VARCHAR(128) PRIMARY KEY, ' +
               'USERNAME VARCHAR(255) NOT NULL, '      +
               'EXPIRES_AT BIGINT NOT NULL, '          +
               'CREATED_AT BIGINT NOT NULL '           +
               ')';

        Exec Sql
          Prepare #CreateRefreshTable From :CreateRefreshTable;

        Exec Sql
          Execute #CreateRefreshTable Using :CreateRefreshTable;

        CompileRPG = 'CRTBNDRPG PGM(' + %Trim(Library) + '/TRANSACT) ' +
                     'SRCFILE(' + %Trim(Library) + '/QRPGLESRC) '      +
                     'DBGVIEW(*SOURCE)';

        Exec Sql
          CALL QSYS2.QCMDEXC(:CompileRPG);

        CreateProcedure =
       'CREATE PROCEDURE '+ %Trim(Library) + '.PROCESSTRANSACTION ( ' +
              'IN AMOUNT DECIMAL(15, 2), '   +
              'IN TTYPE CHAR(1), '           +
              'IN USERNAME VARCHAR(20), '    +
              'OUT BALANCE DECIMAL(15, 2), ' +
              'OUT MESSAGE CHAR(35)) '       +
           'LANGUAGE RPGLE '                 +
           'MODIFIES SQL DATA '              +
           'EXTERNAL NAME ''' + %Trim(%UPPER(Library)) + '/TRANSACT'' '+
           'PARAMETER STYLE GENERAL';

        Exec Sql
          Prepare #CreateProcedure From :CreateProcedure;

        Exec Sql
          Execute #CreateProcedure Using :CreateProcedure;

        *INLR = *ON;
        Return;
