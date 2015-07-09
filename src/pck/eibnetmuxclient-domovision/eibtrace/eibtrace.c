/*
 * eibtrace - eib packet trace
 * 
 * eibnetmux - eibnet/ip multiplexer
 * Copyright (C) 2006-2009 Urs Zurbuchen <going_nuts@users.sourceforge.net>
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 */
 
/*!
 * \example eibtrace.c
 * 
 * Demonstrates usage of the EIBnetmux monitoring function.
 * 
 * It produces a trace of requests seen on the KNX bus.
 */

/*!
 * \cond DeveloperDocs
 * \brief eibtrace - eib packet trace
 */

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include <stdio.h>
#include <stdint.h>
#include <stdlib.h>
#include <string.h>
#include <signal.h>
#include <libgen.h>
#include <getopt.h>
#include <errno.h>
#include <time.h>
#include <math.h>
#include <arpa/inet.h>
#include <sys/time.h>

#ifndef WITH_LOCALHEADERS
#include <eibnetmux/enmx_lib.h>
#else
#include <../src/client_lib/c/enmx_lib.h>
#endif
#include <mylib.h>

/*
 * EIB constants
 */
#define EIB_CTRL_LENGTHTABLE                    0x00
#define EIB_CTRL_LENGTHBYTE                     0x80
#define EIB_CTRL_DATA                           0x00
#define EIB_CTRL_POLL                           0x40
#define EIB_CTRL_REPEAT                         0x00
#define EIB_CTRL_NOREPEAT                       0x20
#define EIB_CTRL_ACK                            0x00
#define EIB_CTRL_NONACK                         0x10
#define EIB_CTRL_PRIO_LOW                       0x0c
#define EIB_CTRL_PRIO_HIGH                      0x04
#define EIB_CTRL_PRIO_ALARM                     0x08
#define EIB_CTRL_PRIO_SYSTEM                    0x00
#define EIB_NETWORK_HOPCOUNT                    0x70
#define EIB_DAF_GROUP                           0x80
#define EIB_DAF_PHYSICAL                        0x00
#define EIB_LL_NETWORK                          0x70
#define T_GROUPDATA_REQ                         0x00
#define A_READ_VALUE_REQ                        0x0000
#define A_WRITE_VALUE_REQ                       0x0080
#define A_RESPONSE_VALUE_REQ                    0x0040

/**
 * cEMI Message Codes
 **/
#define L_BUSMON_IND            0x2B
#define L_RAW_IND               0x2D
#define L_RAW_REQ               0x10
#define L_RAW_CON               0x2F
#define L_DATA_REQ              0x11
#define L_DATA_CON              0x2E
#define L_DATA_IND              0x29
#define L_POLL_DATA_REQ         0x13
#define L_POLL_DATA_CON         0x25
#define M_PROP_READ_REQ         0xFC
#define M_PROP_READ_CON         0xFB
#define M_PROP_WRITE_REQ        0xF6
#define M_PROP_WRITE_CON        0xF5
#define M_PROP_INFO_IND         0xF7
#define M_RESET_REQ             0xF1
#define M_RESET_IND             0xF0


/*
 * Global variables
 */
ENMX_HANDLE     sock_con = 0;
unsigned char   conn_state = 0;


/*
 * local function declarations
 */
static void     Usage( char *progname );
static char     *knx_physical( uint16_t phy_addr );
static char     *knx_group( uint16_t grp_addr );


/*
 * EIB request frame
 */
typedef struct __attribute__((packed)) {
        uint8_t  code;
        uint8_t  zero;
        uint8_t  ctrl;
        uint8_t  ntwrk;
        uint16_t saddr;
        uint16_t daddr;
        uint8_t  length;
        uint8_t  tpci;
        uint8_t  apci;
        uint8_t  data[16];
} CEMIFRAME;


static void Usage( char *progname )
{
    fprintf( stderr, "||||||------------------------------------||||||\n");
    fprintf( stderr, "||||||------------ DOMOVISION ------------||||||\n");
    fprintf( stderr, "||||||------------------------------------||||||\n");

    fprintf( stderr, "Usage: %s [options] [hostname[:port]]\n"
                     "where:\n"
                     "  hostname[:port]                      defines eibnetmux server with default port of 4390\n"
                     "\n"
                     "options:\n"
                     "  -u user                              name of user                           default: -\n"
                     "  -c count                             stop after count number of requests    default: endless\n"
                     "  -q                                   no verbose output (default: no)\n"
                     "\n", basename( progname ));
}


int main( int argc, char **argv )
{
    uint16_t                value_size;
    struct timeval          tv;
    struct tm               *ltime;
    uint16_t                buflen;
    unsigned char           *buf;
    CEMIFRAME               *cemiframe;
    int                     enmx_version;
    int                     c;
    int                     quiet = 0;
    int                     total = -1;
    int                     count = 0;
    int                     spaces = 1;
    char                    *user = NULL;
    char                    pwd[255];
    char                    *target;
    char                    *eis_types;
    int                     hour;
    int                     minute;
    int                     seconds;
    unsigned char           value[20];
    uint32_t                *p_int = 0;
    double                  *p_real;
    
    opterr = 0;
    while( ( c = getopt( argc, argv, "c:u:q" )) != -1 ) {
        switch( c ) {
            case 'c':
                total = atoi( optarg );
                break;
            case 'u':
                user = strdup( optarg );
                break;
            case 'q':
                quiet = 1;
                break;
            default:
                fprintf( stderr, "Invalid option: %c\n", c );
                Usage( argv[0] );
                exit( -1 );
        }
    }
    if( optind == argc ) {
        target = NULL;
    } else if( optind + 1 == argc ) {
        target = argv[optind];
    } else {
        Usage( argv[0] );
        exit( -1 );
    }
    
    // catch signals for shutdown
    signal( SIGINT, Shutdown );
    signal( SIGTERM, Shutdown );
    
    // request monitoring connection
    if( (enmx_version = enmx_init()) != ENMX_VERSION_API ) {
        fprintf( stderr, "Incompatible eibnetmux API version (%d, expected %d)\n", enmx_version, ENMX_VERSION_API );
        exit( -8 );
    }
    sock_con = enmx_open( target, "eibtrace" );
    if( sock_con < 0 ) {
        fprintf( stderr, "Connect to eibnetmux failed (%d): %s\n", sock_con, enmx_errormessage( sock_con ));
        exit( -2 );
    }
    
    // authenticate
    if( user != NULL ) {
        if( getpassword( pwd ) != 0 ) {
            fprintf( stderr, "Error reading password - cannot continue\n" );
            exit( -6 );
        }
        if( enmx_auth( sock_con, user, pwd ) != 0 ) {
            fprintf( stderr, "Authentication failure\n" );
            exit( -3 );
        }
    }
    if( quiet == 0 ) {
        printf( "Connection to eibnetmux '%s' established\n", enmx_gethost( sock_con ));
    }
    
    buf = malloc( 10 );
    buflen = 10;
    if( total != -1 ) {
        spaces = floor( log10( total )) +1;
    }
    while( total == -1 || count < total ) {
        buf = enmx_monitor( sock_con, 0xffff, buf, &buflen, &value_size );
        if( buf == NULL ) {
            switch( enmx_geterror( sock_con )) {
                case ENMX_E_COMMUNICATION:
                case ENMX_E_NO_CONNECTION:
                case ENMX_E_WRONG_USAGE:
                case ENMX_E_NO_MEMORY:
                    fprintf( stderr, "Error on write: %s\n", enmx_errormessage( sock_con ));
                    enmx_close( sock_con );
                    exit( -4 );
                    break;
                case ENMX_E_INTERNAL:
                    fprintf( stderr, "Bad status returned\n" );
                    break;
                case ENMX_E_SERVER_ABORTED:
                    fprintf( stderr, "EOF reached: %s\n", enmx_errormessage( sock_con ));
                    enmx_close( sock_con );
                    exit( -4 );
                    break;
                case ENMX_E_TIMEOUT:
                    fprintf( stderr, "No value received\n" );
                    break;
            }
        } else {
            count++;
            cemiframe = (CEMIFRAME *) buf;
            gettimeofday( &tv, NULL );
            ltime = localtime( &tv.tv_sec );
            if( total != -1 ) {
                printf( "%*d: ", spaces, count );
            }
            printf( "%04d/%02d/%02d - %02d:%02d:%02d",
                       ltime->tm_year + 1900, ltime->tm_mon +1, ltime->tm_mday,
                       ltime->tm_hour, ltime->tm_min, ltime->tm_sec );
            printf( " - phys addr: %8s ", knx_physical( cemiframe->saddr ));
            printf( " - code: " );
			if( cemiframe->code == L_DATA_REQ ) {
                printf( "REQ" );
            } else if( cemiframe->code == L_DATA_CON ) {
                printf( "CON" );
            } else if( cemiframe->code == L_DATA_IND ) {
                printf( "IND" );
            } else if( cemiframe->code == L_BUSMON_IND ) {
                printf( "MON" );
            } else {
                printf( "%02x", cemiframe->code );
            }
			printf( " - priority: " );
            if( cemiframe->ctrl & EIB_CTRL_PRIO_LOW ) {
                printf( "low" );
            } else if( cemiframe->ctrl & EIB_CTRL_PRIO_HIGH ) {
                    printf( "hgh" );
            } else if( cemiframe->ctrl & EIB_CTRL_PRIO_SYSTEM ) {
                    printf( "sys" );
            } else if( cemiframe->ctrl & EIB_CTRL_PRIO_ALARM ) {
                    printf( "alm" );
            }
			printf( " - control: " );
            if( cemiframe->ctrl & EIB_CTRL_REPEAT ) {
                printf( "r" );
            } else {
                printf( " " );
            }
            if( cemiframe->ctrl & EIB_CTRL_ACK ) {
                printf( "k" );
            } else {
                printf( " " );
            }
			printf( " - type: " );
            if( cemiframe->apci & A_WRITE_VALUE_REQ ) {
                printf( "W" );
            } else if( cemiframe->apci & A_RESPONSE_VALUE_REQ ) {
                printf( "A" );
            } else {
                printf( "R" );
            }
			
            printf( " - group addr: %8s", (cemiframe->ntwrk & EIB_DAF_GROUP) ? knx_group( cemiframe->daddr ) : knx_physical( cemiframe->daddr ));
            
			if( cemiframe->apci & (A_WRITE_VALUE_REQ | A_RESPONSE_VALUE_REQ) ) {
                printf( " - value: " );
                
				p_int = (uint32_t *)value;
                p_real = (double *)value;
                
				switch( cemiframe->length ) {
                    case 1:     // EIS 1, 2, 7, 8
                        enmx_frame2value( 1, cemiframe, value );
                        printf( "%s | ", (*p_int == 0) ? "0" : "1" );
                        enmx_frame2value( 2, cemiframe, value );
                        printf( "%d | ", *p_int );
                        enmx_frame2value( 7, cemiframe, value );
                        printf( "%d | ", *p_int );
                        enmx_frame2value( 8, cemiframe, value );
                        printf( "%d", *p_int );
                        eis_types = "1, 2, 7, 8";
                        break;
                    case 2:     // 6, 13, 14
                        enmx_frame2value( 6, cemiframe, value );
                        printf( "%d | %d", *p_int * 100 / 255, *p_int );
                        enmx_frame2value( 13, cemiframe, value );
                        if( *p_int >=  0x20 && *p_int < 0x7f ) {
                            printf( " | %c", *p_int );
                            eis_types = "6, 14, 13";
                        } else {
                            eis_types = "6, 14";
                        }
                        break;
                    case 3:     // 5, 10
                        enmx_frame2value( 5, cemiframe, value );
                        printf( "%.2f | ", *p_real );
                        enmx_frame2value( 10, cemiframe, value );
                        printf( "%d", *p_int );
                        eis_types = "5, 10";
                        break;
                    case 4:     // 3, 4
                        enmx_frame2value( 3, cemiframe, value );
                        seconds = *p_int;
                        hour = seconds / 3600;
                        seconds %= 3600;
                        minute = seconds / 60;
                        seconds %= 60;
                        printf( "%02d:%02d:%02d | ", hour, minute, seconds );
                        enmx_frame2value( 4, cemiframe, value );
                        ltime = localtime( (time_t *)p_int );
                        if( ltime != NULL ) {
                            printf( "%04d/%02d/%02d", ltime->tm_year + 1900, ltime->tm_mon +1, ltime->tm_mday );
                        } else {
                            printf( "inval date" );
                        }
                        eis_types = "3, 4";
                        break;
                    case 5:     // 9, 11, 12
                        enmx_frame2value( 11, cemiframe, value );
                        printf( "%d | ", *p_int );
                        enmx_frame2value( 9, cemiframe, value );
                        printf( "%.2f", *p_real );
                        enmx_frame2value( 12, cemiframe, value );
                        // printf( "12: <->" );
                        eis_types = "9, 11, 12";
                        break;
                    default:    // 15
                        // printf( "%s", string );
                        eis_types = "15";
                        break;
                }
				 printf( " - Hexa: " );
                if( cemiframe->length == 1 ) {
                    printf( "%s", hexdump( &cemiframe->apci, 1, 0 ));
                } else {
                    printf( "%s", hexdump( (unsigned char *)(&cemiframe->apci) +1, cemiframe->length -1, 0 ));
                }
                printf( " - eis types: %s", eis_types );
            }
            printf( "\n" );
	    fflush(stdout);
        }
    }
    return( 0 );
}


/*
 * Return representation of physical device KNX address as string
 */
static char *knx_physical( uint16_t phy_addr )
{
        static char     textual[64];
        int             area;
        int             line;
        int             device;
        
        phy_addr = ntohs( phy_addr );
        
        area = (phy_addr & 0xf000) >> 12;
        line = (phy_addr & 0x0f00) >> 8;
        device = phy_addr & 0x00ff;
        
        sprintf( textual, "%d.%d.%d", area, line, device );
        return( textual );
}


/*
 * Return representation of logical KNX group address as string
 */
static char *knx_group( uint16_t grp_addr )
{
        static char     textual[64];
        int             top;
        int             sub;
        int             group;
        
        grp_addr = ntohs( grp_addr );
        
        top = (grp_addr & 0x7800) >> 11;
        sub = (grp_addr & 0x0700) >> 8;
        group = grp_addr & 0x00ff;
        sprintf( textual, "%d/%d/%d", top, sub, group );
        return( textual );
}