//**
//**  Mercury Mail Transport System - Daemon Interface Definitions
//**  Copyright (c) 1997-99, David Harris, All Rights Reserved.
//**
//**  Note: the structures defined in this file are all BYTE-ALIGNED.
//**  This is very important - if you are using a Borland Compiler
//**  and your project uses WORD or DWORD alignment, then you will
//**  need to add  "#pragma option -a1" before you include this file
//**  to ensure that the compiler aligns the structures correctly.
//**  Getting the byte alignment wrong will almost certainly result
//**  in your Daemon crashing when you load it.
//**

#ifndef _DAEMON_H
#define _DAEMON_H

#ifdef __cplusplus
extern "C" {
#endif

#ifndef INTS_DEFINED
#define INTS_DEFINED
//  INT_16 and UINT_16 must be 16-bit Integer types
typedef unsigned short UINT_16;
typedef short INT_16;
typedef unsigned long UINT_32;
typedef long INT_32;
#endif


//**
//**  Section 1: Data type definitions and constants for lists
//**

#ifndef _LISTS_H

struct _l_node
   {
   unsigned int flags, number;
   struct _l_node *next, *prev;
   void *data;
   };

typedef struct _l_node LNODE;

struct _lv_node
   {
   unsigned int flags, number;
   struct _lv_node *next, *prev;
   BYTE data [];
   };

typedef struct _lv_node LVNODE;

typedef struct
   {
   LNODE *top, *end;          /* pointers to start/end of list */
   int icount;                /* number of items in list */
   unsigned isize;            /* size of *data in LNODE */
   int ilimit;                /* maximum size of list - no limit if 0 */
   int ialloc;                /* whether or not to allocate space for items */
   unsigned int last_acc;     /* Last data accessed using get_list_data */
   LNODE *last_data;          /* "   "   "   "   "   "   "   "   "    " */
   } LIST;

#endif

//**
//**  Section 2: Data type definitions and constants for
//**  Job Management
//**

//  Rewind flags - passed to ji_rewind_job

#define JR_CONTROL 1
#define JR_DATA    2

//  Diagnostic flags - passed to ji_set/get_diagnostics

#define JD_ELEMENT 1
#define JD_JOB 2

typedef struct
   {
   int structlen;
   char jobstatus;
   long jobflags;
   char status;
   char *from;
   char *to;
   long dsize;
   long rsize;
   int total_rcpts;
   int total_failures;
   int total_retries;
   char ip1 [16];
   char ip2 [16];
   char ip3 [16];
   char ip4 [16];
   char jobid [20];
   } JOBINFO;

enum                 //  Job types, for ji_scan_* and ji_create_job
   {
   JT_GENERAL,       //  Local and newly-submitted mail
   JT_OUTGOING,      //  Only mail destined for the outside world
   JT_ANY            //  Any type of job
   };

enum                 //  "mode" values for ji_set_element_status
   {
   JS_COMPLETED,     //  "date" is ignored
   JS_FAILED,        //  "date" is ignored
   JS_RETRY,         //  "date" is used for requeuing if non-NULL
   JS_PENDING,       //  "date" is ignored
   JS_TEMP_FAIL      //  "date" is ignored
   };

enum                 //  "type" values for ji_get_next_element
   {
   JE_ANY,           //  Any type of element is OK
   JE_READY,         //  Only return elements ready to be sent
   JE_FAILED,        //  Only return elements marked as failed
   JE_COMPLETED,     //  Only return elements marked as completed
   JE_PENDING,       //  Only return elements marked as "pending"
   JE_TEMP_FAIL      //  Only return elements marked as temporarily failed
   };


//**
//**  Section 3: General Mercury data type and constant definitions
//**  Many of these structures are not directly used by Daemons.
//**

#ifndef MAXFPATH
#define MAXFPATH 128
#endif

#ifndef size
#define size(x) (sizeof (x) / sizeof (x [0]))
#endif

#define MAXUIC 128
#define MAXHOST 128

//  Statistics manager constants.

#define STC_INTEGER 0
#define STC_STRING 1
#define STC_DATE 2

#define STF_CUMULATIVE 1
#define STF_PEAK 2
#define STF_UNIQUE 4

//  Logging console manager priority constants

#define LOG_DEBUG 25
#define LOG_INFO  20
#define LOG_NORMAL 15
#define LOG_SIGNIFICANT 10
#define LOG_URGENT 5
#define LOG_NONE 0

//  Constants that can be passed to "create_object"

#define OBJ_USER 1
#define OBJ_ADMINISTRATOR 2

typedef unsigned char UCHAR;
typedef unsigned short USHORT;
typedef unsigned long ULONG;

//  INT_16 and UINT_16 must be 16-bit Integer types
typedef unsigned short UINT_16;
typedef short INT_16;

typedef struct
   {
   char auto_forward [60];
   char gw_auto_forward [60];  // Mercury uses this field for forwarding
   char from_alias [60];       // Alternative From: field value
   unsigned flags;
   char security;
   } PMPROP;

typedef struct
   {
   char *name;
   char *domain;
   char *login_name;
   char *password;
   } SERVER_ID;

typedef struct
   {
   char *name;
   char *groupname;
   char *hostname;
   } GROUP_ID;

typedef struct
   {
   char *match;
   char *rewrite;
   } REWRITE;

typedef struct
   {
   char lname [48];
   char fname [128];           // Name of container file for list
   char moderator [80];        // Primary list moderator (if any)
   char title [80];            // Title for list (used in "to" field
   char welcome_file [128];    // File to send to new subscribers
   char farewell_file [128];   // File to send to unsubscribers
   char ispublic;              // NZ if open subscription is available
   char matched;               // NZ if the address passed in is a moderator
   char moderated;             // NZ if mailing to the list is restricted
   char allow_enumeration;     // NZ if anyone may use ENUMERATE
   char reply_to_list;         // NZ if replies should go to the list
   int limit;                  // Maximum allowable number of subscribers
   char errors_to [80];        // Address to which errors should be referred
   char restricted;            // NZ if only list members may mail to the list
   int fanout;                 // Number of jobs to "fan" the delivery to
   char anonymous;             // Whether this list is anonymous or not
   char title_is_address;      // If NZ, the 'title' field contains an address
   char digest_name [14];      // Name of digest file
   unsigned long digest_maxsize;
   int digest_maxwait;
   char archive_file [128];    // File into which to archive messages
   char digest_default;        // If NZ, new users are default to digest mode
   char list_headers;          // Use IETF draft helper headers
   char list_help [80];        // Help URL
   char list_signature [128];  // List's signature file
   char concealed;             // If NZ, do not publicize via the maiser LIST
   long maximum_size;          // Largest message that may be submitted to list
   char password [128];        // Moderator password or password filename
   char pwd_is_filename;       // NZ if "password" is a filename
   } DLIST;

#define FS_BOLD               1
#define FS_ITALIC             2
#define FS_FIXED              4
#define FS_OEMCHARS           8

typedef struct
   {
   char fontname [LF_FACESIZE];
   INT_16 fontsize, style;
   } FONTSPEC;

typedef struct
   {
   char alias [60];
   char obj_name [48];
   char server [48];
   long flags;       /* Unused at present - might be handy later */
   } CHBIND;

typedef struct
   {
   char alias [180], name [180];
   } ALIAS;


//  IMESSAGE structure definition:
//  The IMESSAGE structure is used internally to represent messages
//  and pseudo-messages. This structure exactly parallels a structure
//  used in Pegasus Mail v3.x and later for the same purpose, and is
//  included in this way to allow maximum code portability between
//  the two.

typedef struct
   {
   INT_16 dsize;               //  The size of this data structure
   INT_16 mtype;               //  User-defined message type field
   UINT_32 flags;              //  First bank of message-related flags
   UINT_32 flags2;             //  Second bank of message-related flags
   char fname [14];            //  Recommended filename for message
   char from [42];             //  The sender of the message
   char subject [50];          //  Can you guess what this is?
   UCHAR cdate [8];            //  Timezone-corrected date from message
   UCHAR date [8];             //  Raw RFC822 time and date for message
   UINT_32 fsize;              //  Raw size of this message
   UINT_16 colour;             //  Display colour for this entry
   UINT_16 charset;            //  Character set for message
   char unique_id [34];        //  Unique ID for the message
   void *folder;               //  Currently unused in Mercury/32
   char filename [128];        //  The file containing the message data
   } IMESSAGE;

//  Explanation of fields:
//  "dsize"      The allocated size of this data structure
//  "mtype"      The user can define message types that can be used for sorting
//  "flags"      Can contain any of the flag values shown in Group 1 below
//  "flags2"     Can contain any of the flag values shown in Group 2 below
//  "fname"      Recommended filename for any storage to do with the message
//  "from"       Display version of sender's address
//  "subject"    Display version of message subject
//  "date"       The date as shown in the message's RFC822 "Date:" field
//  "cdate"      The date the message arrived at the local system.
//             - See below for more on the date format
//  "fsize"      Raw size of the message, including headers and formatting
//               Note - no allowance is made for CR/LF conversions.
//  "colour"     Index into colour table for message display colour
//  "charset"    Index into character set table for message charset format
//  "unique_id"  Guaranteed unique persistent global identifier for this message
//  "folder"     The folder in which this message is currently stored.
//
//  Date format: dates in IMESSAGEs use the NetWare 7-byte date format plus an
//  extra byte containing the offset in half-hour units from GMT. The date is
//  always pre-corrected to GMT by WinPMail. Note that byte 0 (the year) is
//  always the actual year - 1900, so the year 2000 is represented by 100.
//  The NetWare date format is as shown:
//
//    Byte 0  - Year - 1900 (i.e, 2005 = 105)
//    Byte 1  - Month (ie, January == 1)
//    Byte 2  - Day (1 .. 31)
//    Byte 3  - Hour (0 - 24)
//    Byte 4  - Minute (0 - 59)
//    Byte 5  - Second (0 - 60)
//    Byte 6  - Day of week (Sunday == 0)   ("255" == "not calculated")

//
//  Group 1 flag values - these can be used in an IMESSAGE "flags" field.
//
#define FILE_MAILED              1  // The message contains a mailed file
#define UUENCODED                2  // The message contains uuencoded data
#define FILE_ATTACHED    0x800003L  // Use this as an attachment mask.
#define ENCRYPTED                4  // The message is encrypted
#define EXPIRED                 16  // The message is past its expiry date
#define FILE_ASCII              32  // Flag in attachment to indicate ASCII file
#define HAS_BEEN_READ          128  // Hey, what do you know! It's been read!
#define ALTERNATIVE          0x100  // The message is Multipart/Alternative type
#define IS_HTML              0x200  // The message is Text/HTML type
#define IS_CIRCULAR          0x400  // The message is being circulated
#define CONFIRMATION        0x2000  // Sender wants confirmation of reading
#define FORWARD            0x8000L  // The message is being forwarded
#define IS_RTF            0x10000L  // Message contains MS-RTF data
#define COPYSELF          0x20000L  // The message is a copy to self
#define DELETED           0x40000L  // The message has been deleted.
#define MIME              0x80000L  // The message is a MIME transmission
#define REPLIED          0x100000L  // The message has been replied to.
#define FORWARDED        0x200000L  // The message has been forwarded.
#define URGENT           0x400000L  // The message is urgent/high priority.
#define BINHEX           0x800000L  // The message is a BinHex file
#define IS_MHS          0x1000000L  // The message originates from MHS
#define IS_SMTP         0x2000000L  // The message originated via SMTP
#define IS_ANNOTATED    0x4000000L  // The message has an annotation
#define ENCLOSURE       0x8000000L  // The message has an enclosure
#define HIGHLIGHTED    0x10000000L  // The message has transient significance
#define MIME_MULTI     0x20000000L  // The message is in MIME Multipart format
#define TEXT_ENRICHED  0x40000000L  // The message is in "text/enriched" format
#define READ_ONLY      0x80000000L  // The message may not be deleted

//
//  Group 2 flag values - these can be used in an IMESSAGE "flags2" field
//

#define IS_NEWMAIL               1  // The message is in the new mail folder
#define IS_NOTICE                2  // The message comes from a noticeboard
#define IS_TEMPORARY             4


//  MIME parsing definitions and structures

enum     //  Content dispositions
   {
   MD_ATTACHMENT, MD_INLINE
   };

enum     // The primary types
   {
   MP_TEXT, MP_MULTIPART, MP_MESSAGE, MP_APPLICATION,
   MP_IMAGE, MP_VIDEO, MP_AUDIO, MP_UNKNOWN
   };

enum     // TEXT subtypes
   {
   MPT_PLAIN, MPT_RICHTEXT, MPT_HTML, MPT_RTF, MPT_UNKNOWN
   };

enum     // MULTIPART subtypes
   {
   MPP_MIXED, MPP_ALTERNATIVE, MPP_DIGEST,
   MPP_PARALLEL, MPP_UNKNOWN
   };

enum     // MESSAGE subtypes
   {
   MPM_RFC822, MPM_PARTIAL, MPM_EXTERNAL_BODY, MPM_UNKOWN
   };

enum     // APPLICATION subtypes
   {
   MPA_OCTET_STREAM, MPA_POSTSCRIPT, MPA_ODA, MPA_BINHEX, MPA_UNKNOWN
   };

enum     // IMAGE subtypes
   {
   MPI_GIF, MPI_JPEG, MPI_UNKNOWN
   };

enum     // VIDEO subtypes
   {
   MPV_MPEG, MPV_UNKNOWN
   };

enum     // AUDIO subtypes
   {
   MPU_BASIC, MPU_UNKNOWN
   };

enum     // MIME transfer-encodings
   {
   //  Note that ME_BINHEX and ME_UUENCODE are handled as special
   //  cases and as such must always appear after ME_UNKNOWN.
   ME_7BIT, ME_8BIT, ME_QUOTED_PRINTABLE, ME_BASE64, ME_UNKNOWN,
   ME_BINHEX, ME_UUENCODE
   };

typedef struct
   {
   char charset [20];
   char *table;
   } MPT;

typedef struct
   {
   char boundary [71];
   LIST partlist;
   } MPP;

typedef struct
   {
   char fname [96];
   char type [20];
   } MPA;

typedef struct
   {
   int primary, secondary, encoding, disposition;
   char p_string [20], s_string [20];
   char description [48];
   char encryptor [16];    //  For encrypted attachments, the encryptor
   int encryptor_flags;
   int section;
   char fname [96];
   union
      {
      MPT mpt;
      MPP mpp;
      MPA mpa;
      IMESSAGE mpm;
      } d;
   } IMIME;


//**
//**  Section 4: Constants for message composition functions
//**

#define OM_M_8BIT 1

#define OM_MT_PLAIN 0         //  A simple, single-part text/plain message
#define OM_MT_MULTIPART 1     //  A multipart/mixed message
#define OM_MT_ALTERNATIVE 2   //  A multipart/alternative message
#define OM_MT_DIGEST 3        //  A multipart/digest type

#define OM_MF_TO 1            //  Set the master recipient of the message
#define OM_MF_SUBJECT 2       //  Set the subject field for the message
#define OM_MF_CC 3            //  Set the secondary recipients of the message
#define OM_MF_FROM 4          //  Set the originator of the message.
#define OM_MF_BODY 5          //  Set the filename containing the message body
#define OM_MF_RAW  6          //  Add a raw header for the message.
#define OM_MF_FLAGS 7         //  Set the message's "flags" field

#define OM_AE_DEFAULT 0       //  Default encoding (MIME BASE64 encoding)
#define OM_AE_TEXT 1          //  Simple textual data, unencoded
#define OM_AE_UUENCODE 2      //  Uuencoding
#define OM_AE_BINHEX 3        //  Macintosh Binhex format (data fork only)

#define OM_AF_INLINE 1        //  Write the file as a simple textual section
#define OM_AF_MESSAGE 2       //  Write the message as a Message/RFC822 part


//**
//**  Section 5: Protocol Module parameter block definition;
//**  Daemons are passed a Protocol Module parameter block.
//**

#define GV_QUEUENAME 1
#define GV_SMTPQUEUENAME 2
#define GV_MYNAME 3
#define GV_TTYFONT 4
#define GV_MAISERNAME 5
#define GV_FRAMEWINDOW 6
#define GV_SYSFONT 7
#define GV_BASEDIR 8
#define GV_SCRATCHDIR 9

#define SYSTEM_PASSWORD 1
#define APOP_SECRET 2
#define PASSWD_MUST_EXIST 256

//  Messages that protocol modules can send using the
//  "mercury_command" function in the protocol parameter block

//  GET_MODULE_INTERFACE:
//    - "parm1" - char * pointer to name of module to locate
//    - Returns: the command interface function for the module, or NULL
#define GET_MODULE_INTERFACE 1

//  ADD_ALIAS
//    - "parm1" - char * pointer to alias to add
//      "parm2" - char * pointer to real-world address string
//    - Returns: NZ on success, 0 on failure
#define ADD_ALIAS 2

//  DELETE_ALIAS
//    - "parm1" - char * pointer to alias field of alias to delete
//      Returns: NZ on success, 0 on failure
#define DELETE_ALIAS 3

//  RESOLVE_ALIAS
//    - "parm1" - char * pointer to buffer to receive address (180 char min)
//      "parm2" - char * pointer to alias to resolve
//    - Returns: NZ if a match was found, 0 if none was found.
#define RESOLVE_ALIAS 4

//  RESOLVE_SYNONYM
//    - "parm1" - char * pointer to buffer to receive address (180 char min)
//      "parm2" - char * pointer to synonym to resolve
//    - Returns: NZ if a match was found, 0 if none was found
#define RESOLVE_SYNONYM 5

//  QUEUE_STATE - enable or disable queue processing
//    - "parm1" - 0 to query current state, 1 to set state
//      "parm2" - 1 to pause processing, 0 to enable it
//    - Returns:  The state of queue processing prior to the call
#define QUEUE_STATE 6

//  DISPLAY_HELP
//    - "parm1" - section number in MERCURY.HLP
//      "parm2" - unused, must be 0
//    - Returns:  Nothing.
#define DISPLAY_HELP 512

#define NOT_IMPLEMENTED 0xF0000000L

#define RFC_822_TIME 0
#define RFC_821_TIME 1

typedef DWORD (*GET_VARIABLE) (int index);
typedef int (*IS_LOCAL_ADDRESS) (char *address, char *uic, char *server);
typedef int (*GET_DELIVERY_PATH) (char *path, char *username, char *host);

typedef int (*IS_GROUP) (char *address, char *host, char *groupname);
typedef int (*PARSE_ADDRESS) (char *target, char *source, int limit);
typedef int (*EXTRACT_ONE_ADDRESS) (char *dest, char *source, int offset);
typedef void (*EXTRACT_CQTEXT) (char *dest, char *source, int len);
typedef int (*DLIST_INFO) (DLIST *dlist, char *lname, int num, char *address,
   char *errbuf, LIST *modlist);
typedef void (*SEND_NOTIFICATION) (char *username, char *host, char *message);
typedef int (*GET_DATE_AND_TIME) (BYTE *tm);
typedef INT_32 (*VERIFY_PASSWORD) (char *username, char *host,
   char *password, INT_32 select);
typedef int (*WRITE_PROFILE) (char *section, char *fname);
typedef int (*MODULE_STATE) (char *modname, int set_value, int state);

//  Job control functions

typedef void * (*JI_SCAN_FIRST_JOB) (int type, int mode, void **data);
typedef void * (*JI_SCAN_NEXT_JOB) (void **data);
typedef void (*JI_END_SCAN) (void **data);

typedef int (*JI_OPEN_JOB) (void *jobhandle);
typedef int (*JI_CLOSE_JOB) (void *jobhandle);
typedef void (*JI_REWIND_JOB) (void *jobhandle, int flags);
typedef int (*JI_DISPOSE_JOB) (void *jobhandle);
typedef int (*JI_PROCESS_JOB) (void *jobhandle);
typedef int (*JI_DELETE_JOB) (void *jobhandle);
typedef int (*JI_ABORT_JOB) (void *jobhandle, int fatal);
typedef int (*JI_GET_JOB_INFO) (void *jobhandle, JOBINFO *ji);

typedef void * (*JI_CREATE_JOB) (int type, char *from,
   unsigned char *start_time);
typedef int (*JI_ADD_ELEMENT) (void *jobhandle, char *address);
typedef int (*JI_ADD_DATA) (void *jobhandle, char *data);
typedef char * (*JI_GET_DATA) (void *jobhandle, char *buffer, int buflen);

typedef char * (*JI_GET_NEXT_ELEMENT) (void *jobhandle, int type, JOBINFO *job);
typedef int (*JI_SET_JOB_FLAGS) (void *jobhandle, long flags);
typedef int (*JI_SET_ELEMENT_STATUS) (void *jobhandle, int mode,
   unsigned char *date);
typedef int (*JI_SET_ELEMENT_RESOLVINFO) (void *jobhandle, char *ip1, char *ip2,
   char *ip3, char *ip4);

typedef int (*JI_SET_DIAGNOSTICS) (void *jobhandle, int forwhat, char *text);
typedef int (*JI_GET_DIAGNOSTICS) (void *jobhandle, int forwhat, char *fname);

typedef void (*JI_INCREMENT_TIME) (unsigned char *tm, unsigned int secs);

typedef long (*JI_TELL) (void *jobhandle, int selector);
typedef int (*JI_SEEK) (void *jobhandle, long ofs, int selector);

typedef void * (*JI_GET_JOB_BY_ID) (char *id);
typedef int (*JI_GET_JOB_TIMES) (void *job, char *submitted, char *ready);

//  MNICA functions

typedef int (*GET_FIRST_GROUP_MEMBER) (char *group, char *host, char *member,
   int mlen, void **data);
typedef int (*GET_NEXT_GROUP_MEMBER) (char *member, int mlen, void **data);
typedef int (*END_GROUP_SCAN) (void **data);
typedef int (*IS_VALID_LOCAL_USER) (char *address, char *username, char *host);
typedef int (*IS_GROUP_MEMBER) (char *host, char *username, char *groupname);
typedef int (*GET_FIRST_USER_DETAILS) (char *host, char *match, char *username,
   int ulen, char *address, int alen, char *fullname, int flen, void **data);
typedef int (*GET_NEXT_USER_DETAILS) (char *username, int ulen, char *address,
   int alen, char *fullname, int flen, void **data);
typedef int (*GET_USER_DETAILS) (char *host, char *match, char *username, int ulen,
   char *address, int alen, char *fullname, int flen);
typedef int (*END_USER_SCAN) (void **data);
typedef void (*READ_PMPROP) (char *userid, char *server, PMPROP *p);
typedef int (*CHANGE_OWNERSHIP) (char *fname, char *host, char *newowner);
typedef int (*BEGIN_SINGLE_DELIVERY) (char *uic, char *server, void **data);
typedef void (*END_SINGLE_DELIVERY) (void **data);

//  Miscellaneous functions - Mercury 2.11 and later only

typedef DWORD (*MERCURY_COMMAND) (DWORD selector, DWORD parm1, DWORD parm2);
typedef char * (*GET_DATE_STRING) (int dtype, char *buf, BYTE *date);
typedef char * (*RFC822_TIME) (char *buffer);
typedef char * (*RFC821_TIME) (char *buffer);

//  File I/O and parsing functions - Mercury 2.15 and later only

typedef INT_32 (*FM_OPEN_FILE) (char *path, UINT_32 flags);
typedef INT_32 (*FM_OPEN_MESSAGE) (IMESSAGE *im, UINT_32 flags);
typedef int (*FM_CLOSE_MESSAGE) (INT_32 id);
typedef char * (*FM_GETS) (char *buf, INT_32 max, INT_32 id);
typedef INT_16 (*FM_GETC) (INT_32 id);
typedef void (*FM_UNGETC) (INT_16 c, INT_32 id);
typedef INT_32 (*FM_READ) (INT_32 id, char *buffer, INT_32 bufsize);
typedef INT_32 (*FM_GETPOS) (INT_32 fil);
typedef INT_16 (*FM_SETPOS) (INT_32 fil, INT_32 offset);
typedef INT_32 (*FM_GET_FOLDED_LINE) (INT_32 fil, char *line, int limit);
typedef char * (*FM_FIND_HEADER) (INT_32 fil, char *name, char *buf, int len);
typedef int (*FM_EXTRACT_MESSAGE) (void *job, char *fname, int flags);

typedef int (*PARSE_HEADER) (INT_32 fil, IMESSAGE *m);
typedef int (*MIME_PREP_MESSAGE) (INT_32 fil, char *fname, int headers);
typedef int (*PARSE_MIME) (INT_32 fil, IMIME *m);
typedef void (*FREE_MIME) (IMIME *m);
typedef int (*FAKE_IMESSAGE) (IMESSAGE *im, char *dest, char *src,
   IMIME *m, char *boundary);
typedef int (*DECODE_MIME_HEADER) (char *dest, char *src);
typedef int (*ENCODE_MIME_HEADER) (char *dest, char *src, int raw);

typedef void * (*OM_CREATE_MESSAGE) (UINT_32 mtype, UINT_32 flags);
typedef INT_32 (*OM_DISPOSE_MESSAGE) (void *mhandle);
typedef INT_32 (*OM_ADD_FIELD) (void *mhandle, UINT_32 selector, char *data);
typedef INT_32 (*OM_ADD_ATTACHMENT) (void *mhandle, char *fname, char *ftype,
   char *description, UINT_32 encoding, UINT_32 flags, void *reserved);
typedef INT_32 (*OM_WRITE_MESSAGE) (void *mhandle, char *fname);
typedef void * (*OM_SEND_MESSAGE) (void *mhandle, char *envelope);

typedef int (*ENCODE_BASE64_STR) (char *dest, char *src, int srclen);
typedef int (*DECODE_BASE64_STR) (char *dest, char *src, char *table);

typedef INT_32 (*ST_REGISTER_MODULE) (char *module_name);
typedef INT_32 (*ST_UNREGISTER_MODULE) (INT_32 mhandle);
typedef INT_32 (*ST_CREATE_CATEGORY) (INT_32 mhandle, char *cname,
   INT_32 ctag, INT_32 ctype, INT_32 dlen, UINT_32 flags);
typedef INT_32 (*ST_REMOVE_CATEGORY) (INT_32 mhandle, UINT_32 ctag);
typedef INT_32 (*ST_SET_HCATEGORY) (INT_32 chandle, UINT_32 data);
typedef INT_32 (*ST_SET_CATEGORY) (INT_32 mhandle, INT_32 ctag, UINT_32 data);

typedef void (*LOGSTRING) (INT_16 ltype, INT_16 priority, char *str);
typedef void (*LOGDATA) (INT_16 ltype, INT_16 priority, char *fmt, ...);

typedef INT_32 (*CREATE_OBJECT) (char *objectname, INT_32 objecttype,
   char *id, INT_32 flags);
typedef INT_32 (*SET_PASSWORD) (char *username, char *host, char *newpassword,
   char *oldpassword, INT_32 select);

typedef INT_32 (*ST_GET_NEXT_MODULE) (INT_32 mhandle, char *modname);
typedef INT_32 (*ST_GET_NEXT_CATEGORY) (INT_32 mhandle, INT_32 chandle,
   char *cname, INT_32 *ctype, INT_32 *clen, INT_32 *cflags);
typedef INT_32 (*ST_GET_CATEGORY_DATA) (INT_32 chandle, void *data);
typedef INT_32 (*ST_EXPORT_STATS) (INT_32 mhandle, char *fname, UINT_32 flags);

typedef INT_32 (*SELECT_PRINTER) (char *devicename, int maxlen);
typedef INT_32 (* PRINT_FILE) (char *fname, char *printername, UINT_32 flags,
   INT_32 lrmargin, INT_32 tbmargin, char *title, char *username, char *fontname,
   INT_32 fontsize);

typedef struct
   {
   long dsize;                              //  Size of this structure
   char vmajor, vminor;
   HWND hMDIParent;
   GET_VARIABLE get_variable;
   IS_LOCAL_ADDRESS is_local_address;
   IS_GROUP is_group;
   PARSE_ADDRESS parse_address;
   EXTRACT_ONE_ADDRESS extract_one_address;
   EXTRACT_CQTEXT extract_cqtext;
   DLIST_INFO dlist_info;
   SEND_NOTIFICATION send_notification;
   GET_DELIVERY_PATH get_delivery_path;
   GET_DATE_AND_TIME get_date_and_time;
   VERIFY_PASSWORD verify_password;
   WRITE_PROFILE write_profile;
   MODULE_STATE module_state;

   //  Job control functions

   JI_SCAN_FIRST_JOB ji_scan_first_job;
   JI_SCAN_NEXT_JOB ji_scan_next_job;
   JI_END_SCAN ji_end_scan;
   JI_OPEN_JOB ji_open_job;
   JI_CLOSE_JOB ji_close_job;
   JI_REWIND_JOB ji_rewind_job;
   JI_DISPOSE_JOB ji_dispose_job;
   JI_PROCESS_JOB ji_process_job;
   JI_DELETE_JOB ji_delete_job;
   JI_ABORT_JOB ji_abort_job;
   JI_GET_JOB_INFO ji_get_job_info;
   JI_CREATE_JOB ji_create_job;
   JI_ADD_ELEMENT ji_add_element;
   JI_ADD_DATA ji_add_data;
   JI_GET_DATA ji_get_data;
   JI_GET_NEXT_ELEMENT ji_get_next_element;
   JI_SET_ELEMENT_STATUS ji_set_element_status;
   JI_SET_ELEMENT_RESOLVINFO ji_set_element_resolvinfo;
   JI_SET_DIAGNOSTICS ji_set_diagnostics;
   JI_GET_DIAGNOSTICS ji_get_diagnostics;
   JI_INCREMENT_TIME ji_increment_time;

   //  MNICA (Network interface) functions

   GET_FIRST_GROUP_MEMBER get_first_group_member;
   GET_NEXT_GROUP_MEMBER get_next_group_member;
   END_GROUP_SCAN end_group_scan;
   IS_VALID_LOCAL_USER is_valid_local_user;
   IS_GROUP_MEMBER is_group_member;
   GET_FIRST_USER_DETAILS get_first_user_details;
   GET_NEXT_USER_DETAILS get_next_user_details;
   GET_USER_DETAILS get_user_details;
   END_USER_SCAN end_user_scan;
   READ_PMPROP read_pmprop;
   CHANGE_OWNERSHIP change_ownership;
   BEGIN_SINGLE_DELIVERY begin_single_delivery;
   END_SINGLE_DELIVERY end_single_delivery;

   //  Miscellaneous functions

   MERCURY_COMMAND mercury_command;
   GET_DATE_STRING get_date_string;
   RFC822_TIME rfc822_time;
   RFC821_TIME rfc821_time;

   //  File parsing and I/O functions

   FM_OPEN_FILE fm_open_file;
   FM_OPEN_MESSAGE fm_open_message;
   FM_CLOSE_MESSAGE fm_close_message;
   FM_GETS fm_gets;
   FM_GETC fm_getc;
   FM_UNGETC fm_ungetc;
   FM_READ fm_read;
   FM_GETPOS fm_getpos;
   FM_SETPOS fm_setpos;
   FM_GET_FOLDED_LINE fm_get_folded_line;
   FM_FIND_HEADER fm_find_header;
   FM_EXTRACT_MESSAGE fm_extract_message;

   PARSE_HEADER parse_header;
   MIME_PREP_MESSAGE mime_prep_message;
   PARSE_MIME parse_mime;
   FREE_MIME free_mime;
   FAKE_IMESSAGE fake_imessage;
   DECODE_MIME_HEADER decode_mime_header;
   ENCODE_MIME_HEADER encode_mime_header;

   OM_CREATE_MESSAGE om_create_message;
   OM_DISPOSE_MESSAGE om_dispose_message;
   OM_ADD_FIELD om_add_field;
   OM_ADD_ATTACHMENT om_add_attachment;
   OM_WRITE_MESSAGE om_write_message;
   OM_SEND_MESSAGE om_send_message;

   ENCODE_BASE64_STR encode_base64_str;
   DECODE_BASE64_STR decode_base64_str;

   ST_REGISTER_MODULE st_register_module;
   ST_UNREGISTER_MODULE st_unregister_module;
   ST_CREATE_CATEGORY st_create_category;
   ST_REMOVE_CATEGORY st_remove_category;
   ST_SET_HCATEGORY st_set_hcategory;
   ST_SET_CATEGORY st_set_category;

   JI_TELL ji_tell;
   JI_SEEK ji_seek;
   JI_SET_JOB_FLAGS ji_set_job_flags;

   LOGSTRING logstring;
   LOGDATA logdata;

   CREATE_OBJECT create_object;
   SET_PASSWORD set_password;

   ST_GET_NEXT_MODULE st_get_next_module;
   ST_GET_NEXT_CATEGORY st_get_next_category;
   ST_GET_CATEGORY_DATA st_get_category_data;
   ST_EXPORT_STATS st_export_stats;

   JI_GET_JOB_BY_ID ji_get_job_by_id;
   JI_GET_JOB_TIMES ji_get_job_times;

   SELECT_PRINTER select_printer;
   PRINT_FILE print_file;
   } M_INTERFACE;


#ifdef USES_M_INTERFACE

//  Convenience macros: allow calls to internal Mercury functions to
//  be made in the same way as they would be in the core code (good
//  for portability).

//  Values for the "flags" field of print_file

#define PRT_MESSAGE 1         //  Print as an RFC822 message
#define PRT_REFORMAT 2        //  Reformat long lines when printing
#define PRT_TIDY 4            //  Print only "important" headers
#define PRT_FOOTER 8          //  Print a footer on each page
#define PRT_NOHEADERS 16      //  Print no message headers
#define PRT_FIRSTONLY 32      //  Print only first line of headers
#define PRT_ITALICS 64        //  Print quoted text in italics

extern M_INTERFACE *mi;

#define get_variable(x) (mi->get_variable (x))
#define is_local_address(a,u,s) (mi->is_local_address (a, u, s))
#define is_group(a,h,g) (mi->is_group (a, h, g))
#define parse_address(t,s,l) (mi->parse_address (t, s, l))
#define extract_one_address(d,s,o) (mi->extract_one_address (d, s, o))
#define extract_cqtext(d,s,l) (mi->extract_cqtext (d, s, l))
#define dlist_info(d,l,n,a,e,m) (mi->dlist_info(d, l, n, a, e, m))
#define send_notification(u,h,m) (mi->send_notification (u, h, m))
#define get_delivery_path(p,u,h) (mi->get_delivery_path (p, u, h))
#define get_date_and_time(b) (mi->get_date_and_time (b))
#define verify_password(u,s,p,e) (mi->verify_password (u, s, p, e))
#define write_profile(s,f) (mi->write_profile (s, f))
#define module_state(m,v,s) (mi->module_state (m, v, s))

#define ji_scan_first_job(t,m,d) (mi->ji_scan_first_job (t,m,d))
#define ji_scan_next_job(d) (mi->ji_scan_next_job (d))
#define ji_end_scan(d) (mi->ji_end_scan (d))
#define ji_open_job(j) (mi->ji_open_job (j))
#define ji_close_job(j) (mi->ji_close_job (j))
#define ji_rewind_job(j,f) (mi->ji_rewind_job (j,f))
#define ji_dispose_job(j) (mi->ji_dispose_job (j))
#define ji_process_job(j) (mi->ji_process_job (j))
#define ji_delete_job(j) (mi->ji_delete_job (j))
#define ji_abort_job(j,f) (mi->ji_abort_job (j, f))
#define ji_get_job_info(j,i) (mi->ji_get_job_info (j, i))
#define ji_create_job(t,f,s) (mi->ji_create_job (t,f,s))
#define ji_add_element(j,a) (mi->ji_add_element (j,a))
#define ji_add_data(j,d) (mi->ji_add_data (j,d))
#define ji_get_data(j,b,l) (mi->ji_get_data (j,b,l))
#define ji_get_next_element(j,t,i) (mi->ji_get_next_element (j,t,i))
#define ji_set_element_status(j,m,d) (mi->ji_set_element_status (j,m,d))
#define ji_set_element_resolvinfo(j,k,l,m,n) (mi->ji_set_element_resolvinfo (j,k,l,m,n))
#define ji_set_diagnostics(j,w,t) (mi->ji_set_diagnostics (j,w,t))
#define ji_get_diagnostics(j,w,f) (mi->ji_get_diagnostics (j,w,f))
#define ji_increment_time(t,s) (mi->ji_increment_time (t,s))
#define ji_tell(j,s) (mi->ji_tell (j,s))
#define ji_seek(j,o,s) (mi->ji_seek(j,o,s))
#define ji_set_job_flags(j,f) (mi->ji_set_job_flags(j,f))
#define ji_get_job_by_id(i) (mi->ji_get_job_by_id(i))
#define ji_get_job_times(j,s,r) (mi->ji_get_job_times(j,s,r))

#define get_first_group_member(g,h,m,l,d) (mi->get_first_group_member(g,h,m,l,d))
#define get_next_group_member(m,l,d) (mi->get_next_group_member(m,l,d))
#define end_group_scan(d) (mi->end_group_scan(d))
#define is_valid_local_user(a,u,h) (mi->is_valid_local_user(a,u,h))
#define is_group_member(h,u,g) (mi->is_group_member(h,u,g))
#define get_first_user_details(h,n,u,ul,a,al,f,fl,d) (mi->get_first_user_details(h,n,u,ul,a,al,f,fl,d))
#define get_next_user_details(u,ul,a,al,f,fl,d) (mi->get_next_user_details(u,ul,a,al,f,fl,d))
#define get_user_details(h,m,u,ul,a,al,f,fl) (mi->get_user_details(h,m,u,ul,a,al,f,fl))
#define end_user_scan(d) (mi->end_user_scan(d))
#define read_pmprop(u,s,p) (mi->read_pmprop(u,s,p))
#define change_ownership(f,h,n) (mi->change_ownership(f,h,n))
#define begin_single_delivery(u,s,d) (mi->begin_single_delivery(u,s,d))
#define end_single_delivery(d) (mi->end_single_delivery(d))

#define mercury_command(s,p1,p2) (mi->mercury_command(s,p1,p2))
#define get_date_string(s,b,d) (mi->get_date_string(s,b,d))
#define rfc822_time(s) (mi->rfc822_time(s))
#define rfc821_time(s) (mi->rfc821_time(s))

#define fm_open_file(p,f) (mi->fm_open_file(p,f))
#define fm_open_message(i,f) (mi->fm_open_message(i,f))
#define fm_close_message(i) (mi->fm_close_message(i))
#define fm_gets(b,m,i) (mi->fm_gets(b,m,i))
#define fm_getc(i) (mi->fm_getc(i))
#define fm_ungetc(c,i) (mi->fm_ungetc(c,i))
#define fm_read(i,b,s) (mi->fm_read(i,b,s))
#define fm_getpos(f) (mi->fm_getpos(f))
#define fm_setpos(f,o) (mi->fm_setpos(f,o))
#define fm_get_folded_line(f,l,x) (mi->fm_get_folded_line(f,l,x))
#define fm_find_header(i,n,b,l) (mi->fm_find_header(i,n,b,l))
#define fm_extract_message(j,n,f) (mi->fm_extract_message(j,n,f))

#define parse_header(f,m) (mi->parse_header(f,m))
#define mime_prep_message(i,f,h) (mi->mime_prep_message(i,f,h))
#define parse_mime(i,m) (mi->parse_mime(i,m))
#define free_mime(m) (mi->free_mime(m))
#define fake_imessage(i,s,m,e,b) (mi->fake_imessage(i,s,m,e,b))
#define decode_mime_header(d,s) (mi->decode_mime_header(d,s))
#define encode_mime_header(d,s,r) (mi->encode_mime_header(d,s,r))

#define om_create_message(m,f) (mi->om_create_message(m,f))
#define om_dispose_message(m) (mi->om_dispose_message(m))
#define om_add_field(m,s,d) (mi->om_add_field(m,s,d))
#define om_add_attachment(m,f,t,d,e,g,r) (mi->om_add_attachment(m,f,t,d,e,g,r))
#define om_write_message(m,f) (mi->om_write_message(m,f))
#define om_send_message(m,e) (mi->om_send_message(m,e))

#define encode_base64_str(d,s,l) (mi->encode_base64_str(d,s,l))
#define decode_base64_str(d,s,t) (mi->decode_base64_str(d,s,t))

#define st_register_module(m) (mi->st_register_module(m))
#define st_unregister_module(h) (mi->st_unregister_module(h))
#define st_create_category(m,c,t,y,l,f) (mi->st_create_category(m,c,t,y,l,f))
#define st_remove_category(m,c) (mi->st_remove_category(m,c))
#define st_set_hcategory(c,d) (mi->st_set_hcategory(c,d))
#define st_set_category(m,c,d) (mi->st_set_category(m,c,d))

#define logstring(l,p,s) (mi->logstring(l,p,s))
// "logdata" has variable parameters and cannot be accessed via a macro

#define create_object(n,t,i,f) (mi->create_object(n,t,i,f))
#define set_password(u,h,n,o,s) (mi->set_password(u,h,n,o,s))

#define st_get_next_module(m,n) (mi->st_get_next_module(m,n))
#define st_get_next_category(m,h,c,t,l,f) (mi->st_get_next_category(m,h,c,t,l,f))
#define st_get_category_data(c,d) (mi->st_get_category_data(c,d))
#define st_export_stats(m,f,l) (mi->st_export_stats(m,f,l))

#define select_printer(d,m) (mi->select_printer(d,m))
#define print_file(f,p,l,m,b,t,u,n,z) (mi->print_file(f,p,l,m,b,t,u,n,z))

#endif  //  USES_M_INTERFACE

#ifdef __cplusplus
};
#endif

#endif  //  _DAEMON_H

