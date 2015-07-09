/* 
 * eibread - read data from eib
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
 * \example eibread.c
 * 
 * Demonstrates usage of the EIBnetmux group reading and data decoding functions.
 */

/*!
 * \cond DeveloperDocs
 * \brief eibread - read data from eib
 */

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include <stdio.h>
#include <stdint.h>
#include <stdlib.h>
#include <libgen.h>
#include <string.h>
#include <getopt.h>
#include <time.h>

#ifndef WITH_LOCALHEADERS
#include <eibnetmux/enmx_lib.h>
#else
#include <../src/client_lib/c/enmx_lib.h>
#endif
#include <mylib.h>

#define  FALSE          0
#define  TRUE           1


/*
 * Global variables
 */
ENMX_HANDLE     sock_con = 0;
unsigned char   conn_state = 0;


/*
 * local function declarations
 */
static void     Usage( char *prog );

static void Usage( char *prog )
{
    fprintf( stderr, "||||||------------------------------------||||||\n");
    fprintf( stderr, "||||||------------ DOMOVISION ------------||||||\n");
    fprintf( stderr, "||||||------------------------------------||||||\n");

    fprintf( stderr, "%s [options] [e:<eis>] <group address> ...\n", basename( prog ));
    fprintf( stderr, "options:\n" );
    fprintf( stderr, "  -s server           name of eibnetmux server (default: <search>)\n" );
    fprintf( stderr, "  -u user             name of user (default: -)\n" );
    fprintf( stderr, "  -q                  no verbose output (default: no)\n\n" );
    fprintf( stderr, "<group address> is knx group address either as x/y/z or 16-bit integer\n" );
    fprintf( stderr, "<eis type> is an integer in the range 0-15 where 0 indicates auto detection\n" );
}


int main( int argc, char **argv )
{
    int             show_usage;
    uint16_t        knxaddress = 0;
    uint16_t        eis = 0;
    char            *user = NULL;
    char            pwd[255];
    char            *server = NULL;
    int             enmx_version;
    int             c;
    int             quiet = 0;
    uint16_t        len;
    int             type;
    unsigned char   *data;
    unsigned char   value[20];
    int             number = 0;
    double          real = 0;
    int             value_integer;
    uint32_t        *p_int = 0;
    double          *p_real;
    struct tm       *gtime;
    struct tm       *ltime;
    
    // get parameters
    show_usage = FALSE;
    opterr = 0;
    while( ( c = getopt( argc, argv, "s:u:q" )) != -1 ) {
        switch( c ) {
            case 's':
                server = strdup( optarg );
                break;
            case 'u':
                user = strdup( optarg );
                break;
            case 'e':
                eis = atoi( optarg );
                if( eis > 15 ) {
                    Usage( argv[0] );
                    exit( -1 );
                }
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
        Usage( argv[0] );
        exit( -1 );
    }
    
    // read data from bus
    if( (enmx_version = enmx_init()) != ENMX_VERSION_API ) {
        fprintf( stderr, "Incompatible eibnetmux API version (%d, expected %d)\n", enmx_version, ENMX_VERSION_API );
        exit( -8 );
    }
    
    sock_con = enmx_open( server, "eibread" );
    if( sock_con < 0 ) {
        fprintf( stderr, "Connect to eibnetmux failed: %s\n", enmx_errormessage( sock_con ));
        exit( -2 );
    }
    if( quiet == 0 ) {
        //printf( "Connecting to eibnetmux server on '%s'\n", enmx_gethost( sock_con ));
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
        //printf( "Connection established\n" );
    }
    
    while( optind < argc ) {
        if( strncmp( argv[optind], "e:", 2 ) == 0 ) {
            eis = atoi( argv[optind++] +2 );
            continue;
        }
        
        // knx group address
        if( strchr( argv[optind], '/' ) == NULL ) {
            if( sscanf( argv[optind], "%d", &value_integer ) != 1 ) {
                show_usage = TRUE;
                break;
            } else {
                knxaddress = value_integer & 0xffff;
            }
        } else {
            if( (knxaddress = enmx_getaddress( argv[optind] )) == 0xffff ) {
                show_usage = TRUE;
                break;
            }
        }
        
        // read data
        data = enmx_read( sock_con, knxaddress, &len );
        //printf( "%-8s ", argv[optind] );
        p_int = (uint32_t *)value;
        p_real = (double *)value;
        
        if( data != NULL ) {
            if( eis != 0 ) {
                type = enmx_eis2value( eis, data, len, value );
                switch( type ) {
                    case enmx_KNXerror:
                        printf( "%s - unable to convert data for eis %d", hexdump( data, len, 1 ), eis );
                        break;
                    case enmx_KNXinteger:
                        number = *p_int;
                        switch( eis ) {
                            case 1:
                                printf( (number == 0) ? "off" : "on" );
                                break;
                            case 2:
                                printf( "%01x", number );
                                break;
                            case 3:
                                gtime = gmtime( (time_t *)p_int ) ;
                                printf( "%02d:%02d:%02d", gtime->tm_hour, gtime->tm_min, gtime->tm_sec );
                                break;
                            case 4:
                                ltime = localtime( (time_t *)p_int );
                                printf( "%04d/%02d/%02d", ltime->tm_year + 1900, ltime->tm_mon +1, ltime->tm_mday );
                                break;
                            case 6:
                                printf( "%d", number );
                                break;
                            case 7:
                                printf( "%d", number );
                                break;
                            case 8:
                                printf( "%d", number );
                                break;
                            case 10:
                                printf( "%d (%04X)", number, number );
                                break;
                            case 11:
                                printf( "%d (%08X)", number, number );
                                break;
                            case 13:
                                printf( "%c", number );
                                break;
                            case 14:
                                printf( "%d", number );
                                break;
                        }
                        break;
                    case enmx_KNXfloat:
                        real = *p_real;
                        printf( "%.2f", real );
                        break;
                    case enmx_KNXchar:
                        printf( "%c", *value );
                        break;
                    case enmx_KNXstring:
                        printf( "%*s", len, value );
                        break;
                }
            } else {
                //printf( "%d byte%s ", len, (len != 1) ? "s" : " " );
				/*
                switch( len ) {
                    case 1:     // EIS 1, 2, 7, 8
                        printf( "%-12s  ", "[1, 2, 7, 8]" );
                        type = enmx_eis2value( 1, data, len, value );
                        printf( "%s | ", (*p_int == 0) ? "off" : "on" );
                        type = enmx_eis2value( 2, data, len, value );
                        printf( "%d | ", *p_int );
                        type = enmx_eis2value( 7, data, len, value );
                        printf( "%d | ", *p_int );
                        type = enmx_eis2value( 8, data, len, value );
                        printf( "%d", *p_int );
                        break;
                    case 2:     // 6, 13, 14
                        printf( "%-12s  ", "[6, 13, 14]" );
                        type = enmx_eis2value( 6, data, len, value );
                        printf( "%d , %d%%", *p_int, *p_int * 100 / 255 );
                        type = enmx_eis2value( 13, data, len, value );
                        if( *p_int >=  0x20 && *p_int < 0x7f ) {
                            printf( " | %c", *p_int );
                        }
                        break;
                    case 3:     // 5, 10
                        printf( "%-12s  ", "[5, 10]" );
                        type = enmx_eis2value( 5, data, len, value );
                        printf( "%.2f | ", *p_real );
                        type = enmx_eis2value( 10, data, len, value );
                        printf( "%d", *p_int );
                        break;
                    case 4:     // 3, 4
                        printf( "%-12s  ", "[3, 4]" );
                        type = enmx_eis2value( 3, data, len, value );
                        gtime = gmtime( (time_t *)p_int ) ;
                        printf( "%02d:%02d:%02d | ", gtime->tm_hour, gtime->tm_min, gtime->tm_sec );
                        type = enmx_eis2value( 4, data, len, value );
                        ltime = localtime( (time_t *)p_int );
                        printf( "%04d/%02d/%02d", ltime->tm_year + 1900, ltime->tm_mon +1, ltime->tm_mday );
                        break;
                    case 5:     // 9, 11, 12
                        printf( "%-12s  ", "[9, 11, 12]" );
                        type = enmx_eis2value( 11, data, len, value );
                        printf( "%d | ", *p_int );
                        type = enmx_eis2value( 9, data, len, value );
                        printf( "%.2f", *p_real );
                        type = enmx_eis2value( 12, data, len, value );
                        // printf( "12: <->" );
                        break;
                    default:    // 15
                        // printf( "%s", string );
                        break;
                }
				*/
				if( len == 1 ) {
                    printf( "%s", hexdump( data, 1, 0 ));
                } else {
                   printf( "%s", hexdump( (unsigned char *)data +1, len -1, 0 ) );
                }
				
            }
            free( data );
        } else {
            printf( "ERROR - No date response" );
        }
        printf( "\n" );
        
        optind++;
    }
    
    if( show_usage == TRUE ) {
        Usage( basename( argv[0] ));
        exit( -1 );
    }
    
    enmx_close( sock_con );

    exit( 0 );
}
