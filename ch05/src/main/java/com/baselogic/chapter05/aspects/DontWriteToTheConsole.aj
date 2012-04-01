package com.baselogic.chapter05.aspects;

public aspect DontWriteToTheConsole {

    // This pointcut complains about any use of System.out or System.err
    //pointcut sysOutOrErrAccess() : get(* System.out) || get(* System.err);
    pointcut sysOutOrErrAccess() : get(* System.err);

    declare error
      : sysOutOrErrAccess()
      : "Don't write to the console";

}
