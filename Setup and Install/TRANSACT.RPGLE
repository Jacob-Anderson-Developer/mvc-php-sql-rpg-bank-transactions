       ctl-opt option(*nodebugio:*srcstmt) ;

       Dcl-F BALANCELOG Disk Usage(*Input:*Output) Keyed;

       Dcl-S Withdrawal Packed(15:2) Inz(*Zeros);
       Dcl-S Deposit    Packed(15:2) Inz(*Zeros);
       Dcl-S TransType  Char(1);

       DCL-PI *n;
         Amt            Packed(15:2);
         TType          Char(1);
         User_Name      VarChar(20);
         Balance        Packed(15:2);
         Message        Char(35);
       END-PI;

       ExSr GetBalance;

       If TType = *Blank;
         *INLR = *On;
         Return;
       EndIf;

       // Assign value to db fields
       TransType = TType;
       TransTime = %Timestamp(*Sys);
       Amount = Amt;
       USERNAME = User_Name;

       If TType = 'W' And Amt > Balance;
         Message = 'Withdrawal Amount Exceeds Balance';
         *INLR = *On;
         Return;
       Else;
         Write Bal;
         Message = 'Transaction Successful';
       EndIf;

       Clear Amount;
       Clear Withdrawal;
       Clear Deposit;

       ExSr GetBalance;

       *inlr = *on;
       return;

       BegSr GetBalance;

         SetLL (User_Name) BalanceLog;

         Read(e) BalanceLog;

         Dow Not %Eof(BalanceLog) And %Equal(BalanceLog);
           If TRANSTYPE = 'W';
             Withdrawal += Amount;
           Else;
             Deposit += Amount;
           EndIf;
           Read BalanceLog;
         EndDo;

         Balance = Deposit - Withdrawal;

       EndSr;
