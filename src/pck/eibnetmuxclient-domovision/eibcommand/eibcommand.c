/*
 * eibcommand - send request to eib
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
 * \example eibcommand.c
 * 
 * Demonstrates usage of the EIBnetmux group writing and data encoding functions.
 */

/*!
 * \cond DeveloperDocs
 * \brief eibcommand - send request to eib
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
    fprintf( stderr, "%s [options] <group address> <eis type> <value>\n", basename( prog ));
    fprintf( stderr, "options:\n" );
    fprintf( stderr, "  -s server           name of eibnetmux server (default: <search>)\n" );
    fprintf( stderr, "  -u user             name of user (default: -)\n" );
    fprintf( stderr, "  -q                  no verbose output (default: no)\n\n" );
    fprintf( stderr, "<group address> is knx group address either as x/y/z or 16-bit integer\n" );
    fprintf( stderr, "<eis type> is an integer in the range 1-15\n" );
    fprintf( stderr, "<value> depends on eis type:\n" );
    fprintf( stderr, "        1,2,6,7,8,13,14 - byte\n" );
    fprintf( stderr, "        3               - time in seconds\n" );
    fprintf( stderr, "        4               - date in seconds\n" );
    fprintf( stderr, "        5               - 5 redirect to 6 : Dirty shit but ok for my need\n" );
    fprintf( stderr, "        9               - float \n" );
    fprintf( stderr, "       10               - integer 16-bit\n" );
    fprintf( stderr, "       11               - integer 32-bit\n" );
    fprintf( stderr, "       13               - 4 bytes\n" );
    fprintf( stderr, "       15               - 14 bytes\n" );
}


int main( int argc, char **argv )
{
    int             show_usage;
    uint16_t        knxaddress = 0;
    uint16_t        eis;
    int             enmx_version;
    int             quiet = 0;
    int             c;
    int             len;
    unsigned char   value_char;
    int             value_integer;
    uint32_t        value_int32;
    float           value_float;
    char            *string = NULL;
    unsigned char   *data;
    unsigned char   *p_val = NULL;
    char            *user = NULL;
    char            pwd[255];
    char            *server = NULL;
    
    // get parameters
    show_usage = FALSE;
    opterr = 0;
    while( ( c = getopt( argc, argv, "qs:u:" )) != -1 ) {
        switch( c ) {
            case 's':
                server = strdup( optarg );
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
    if( optind + 3 != argc ) {
        show_usage = TRUE;
    } else {
        // knx group address
        if( strchr( argv[optind +0], '/' ) == NULL ) {
            if( sscanf( argv[optind +0], "%d", &value_integer ) != 1 ) {
                show_usage = TRUE;
            } else {
                knxaddress = value_integer & 0xffff;
            }
        } else {
            if( (knxaddress = enmx_getaddress( argv[optind +0] )) == 0xffff ) {
                show_usage = TRUE;
            }
        }
        // eis type
        if( sscanf( argv[optind +1], "%d", &value_integer ) != 1 || value_integer < 1 || value_integer > 15 ) {
            show_usage = TRUE;
        } else {
            eis = value_integer & 0x0f;
	    //Modification DOMOVISION, Ok c'est degeux mais pour le moment ca fait l'affaire
	    if (eis == 5){
	     eis=6;
	    }
            switch( eis ) {
                case 1:
                case 2:
                case 3:
                case 4:
		//case 5:
                case 6:
                case 7:
                case 8:
                case 10:
                case 14:
                    if( sscanf( argv[optind +2], "%d", &value_integer ) != 1 ) {
                        show_usage = TRUE;
                    }
                    p_val = (unsigned char *)&value_integer;
                    break;
                case 11:
                    if( sscanf( argv[optind +2], "%d", &value_int32 ) != 1 ) {
                        show_usage = TRUE;
                    }
                    p_val = (unsigned char *)&value_int32;
                    break;
                case 5:
                case 9:
                    if( sscanf( argv[optind +2], "%f", &value_float ) != 1 ) {
                        show_usage = TRUE;
                    }
                    p_val = (unsigned char *)&value_float;
                    break;
                case 13:
                    if( sscanf( argv[optind +2], "%c", &value_char ) != 1 ) {
                        show_usage = TRUE;
                    }
                    p_val = (unsigned char *)&value_char;
                    break;
                case 15:
                    string = argv[optind +2];
                    p_val = (unsigned char *)string;
                    break;
                case 12:
                    break;
            }
        }
    }
    
    if( show_usage == TRUE ) {
        Usage( basename( argv[0] ));
        exit( -1 );
    }
    
    // write command to bus
    if( (enmx_version = enmx_init()) != ENMX_VERSION_API ) {
        fprintf( stderr, "Incompatible eibnetmux API version (%d, expected %d)\n", enmx_version, ENMX_VERSION_API );
        exit( -8 );
    }

    if( (data = malloc( enmx_EISsizeKNX[eis] )) == NULL ) {
        fprintf( stderr, "Out of memory\n" );
        exit( -4 );
    }
    if( enmx_value2eis( eis, (void *)p_val, data ) != 0 ) {
        fprintf( stderr, "Error in value conversion\n" );
        exit( -5 );
    }
    
    sock_con = enmx_open( server, "eibcommand" );
    if( sock_con < 0 ) {
        fprintf( stderr, "Connect to eibnetmux failed: %s\n", enmx_errormessage( sock_con ));
        exit( -2 );
    }
    if( quiet == 0 ) printf( "Connecting to eibnetmux server on '%s'\n", enmx_gethost( sock_con ));
    
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
    if( quiet == 0 ) printf( "Connection established\n" );
    
    len = (eis != 15) ? enmx_EISsizeKNX[eis] : strlen( string );
    if( enmx_write( sock_con, knxaddress, len, data ) != 0 ) {
        fprintf( stderr, "Unable to send command: %s\n", enmx_errormessage( sock_con ));
        exit( -7 );
    }
    
    if( quiet == 0 ) printf( "Request sent to %s\n", enmx_getgroup( knxaddress ));
    
    enmx_close( sock_con );
    
    if( quiet == 0 ) printf( "Connection closed\n" );
    
    exit( 0 );
}

/*!
 * \endcond
 */
