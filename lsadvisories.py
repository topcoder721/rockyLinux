import os
import sys
import json
import re
import mysql.connector
from mysql.connector import Error
from dotenv import load_dotenv

# Load environment variables
load_dotenv()


def get_db_connection():
    """Create and return database connection using environment variables."""
    try:
        connection = mysql.connector.connect(
            host=os.getenv('DB_HOST', 'localhost'),
            database=os.getenv('DB_NAME'),
            user=os.getenv('DB_USER'),
            password=os.getenv('DB_PASSWORD'),
            port=int(os.getenv('DB_PORT', 3306))
        )
        return connection
    except Error as e:
        print(f"Error connecting to database: {e}")
        return None


def idl_set_section(sections_array, line):
    """Set section flags based on line content."""
    for key in sections_array.keys():
        if key in line:
            # Reset all sections to False
            for k in sections_array.keys():
                sections_array[k] = False
            # Set current section to True
            sections_array[key] = True
            break
    return sections_array


def array_to_paragraphs(data):
    """Convert array to paragraphs."""
    index = 0
    array2 = {}
    
    for k, line in enumerate(data['array']):
        skip_flag = False
        
        for skip in data['skip']:
            if skip in line:
                skip_flag = True
        
        if skip_flag or (k == 0 and line.isspace()):
            continue
        
        if line.isspace():
            if index > 0 and index in array2:
                array2[index] += "<br/>"
            index += 1
            continue
        
        if index not in array2:
            array2[index] = ''
        
        array2[index] += line
    
    return array2


def main():
    """Main function to process advisory article."""
    # Get article ID from command line argument
    if len(sys.argv) < 2:
        print("Usage: python lsadvisories.py <article_id>")
        sys.exit(1)
    
    article_id = int(sys.argv[1])
    
    # Get database connection
    db = get_db_connection()
    if not db:
        print("Failed to connect to database")
        sys.exit(1)
    
    cursor = db.cursor(dictionary=True)
    
    # Load article from database
    try:
        cursor.execute("SELECT * FROM #__content WHERE id = %s", (article_id,))
        article = cursor.fetchone()
        
        if not article:
            print(f"Article with ID {article_id} not found")
            cursor.close()
            db.close()
            sys.exit(1)
    except Error as e:
        print(f"Error loading article: {e}")
        cursor.close()
        db.close()
        sys.exit(1)
    
    # Extract article data
    date_c = article['created']
    introtext_n = article['introtext']
    
    # Truncate introtext
    needle = '. '
    if needle in introtext_n:
        introtext = introtext_n.split(needle)[0]
    else:
        introtext = introtext_n
    
    # Oracle intro issue fix
    if introtext.strip() == "" and article['introtext'] != "":
        introtext = article['introtext']
    
    introtext = introtext[:180] + '...' if len(introtext) > 180 else introtext
    
    title = article['title']
    itemid = article['id']
    catitemid = article['catid']
    
    final_arr = {}
    
    # Clean fulltext
    fulltext = article['fulltext']
    replacements = [
        '<title>', '<textarea>', '<head>', '</head>', '<body>', '</body>',
        '-->', '<!--', '<html>', '</html>', '<canvas>', '</canvas>',
        '<body bgcolor="#FFFFFF" text="#000000">', '<plaintext>',
        '<iframe>', ' <meta name="referrer"> ', '<select>', '<template>',
        '<iframe src>'
    ]
    
    for rep in replacements:
        fulltext = fulltext.replace(rep, '')
    
    fulltext = fulltext.replace('<iframe>', 'iframe')
    fulltext = fulltext.replace(' <meta name="referrer"> ', 'meta name = refer')
    fulltext = fulltext.replace('<select>', 'select')
    fulltext = fulltext.replace('<template>', 'template')
    fulltext = fulltext.replace('<iframe src>', 'iframe src')
    
    arr = fulltext.split("\n")
    fallback = False
    
    # Initialize section arrays based on category
    sections_array = {}
    
    # Gentoo (91)
    if catitemid == 91:
        sections_array = {
            "Synopsis": False,
            "Background": False,
            "Affected packages": False,
            "Description": False,
            "Impact": False,
            "Workaround": False,
            "Resolution": False,
            "References": False,
            "Availability": False,
            "Concerns": False,
            "License": False,
        }
    
    # Oracle (217)
    elif catitemid == 217:
        sections_array = {
            "i386:": False,
            "x86_64:": False,
            "SRPMS:": False,
            "aarch64:": False,
            "Description of changes:": False,
            "Related CVEs:": False,
        }
    
    # Mageia (203)
    elif catitemid == 203:
        sections_array = {
            "Synopsis": False,
            "Publication date:": False,
            "URL:": False,
            "Type:": False,
            "Affected Mageia releases:": False,
            "CVE:": False,
            "References:": False,
            "SRPMS:": False,
        }
    
    # RedHat (98)
    elif catitemid == 98:
        sections_array = {
            "Synopsis:": False,
            "Advisory ID:": False,
            "Product:": False,
            "Advisory URL:": False,
            "Issue date:": False,
            "CVE Names:": False,
            "Summary:": False,
            "Relevant releases/architectures:": False,
            "Problem description:": False,
            "Description:": False,
            "Topic:": False,
            "Solution:": False,
            "Bugs fixed": False,
            "Package List:": False,
            "References:": False,
            "Contact:": False,
        }
    
    # ScientificLinux (200)
    elif catitemid == 200:
        sections_array = {
            "Synopsis:": False,
            "Advisory ID:": False,
            "Issue Date:": False,
            "CVE Numbers:": False,
            "Security Fix(es):": False,
            "Bug Fix(es):": False,
        }
    
    # Slackware (99)
    elif catitemid == 99:
        sections_array = {
            "Here are the details from the Slackware": False,
            "Where to find the new packages:": False,
            "MD5 signatures:": False,
            "Installation instructions:": False,
        }
    
    # SUSE (100)
    elif catitemid == 100:
        sections_array = {
            "Container Advisory ID :": False,
            "Container Tags        :": False,
            "Container Release     :": False,
            "Severity              :": False,
            "Type                  :": False,
            "References            :": False,
        }
        separator = 0
    
    # OpenSUSE (202)
    elif catitemid == 202:
        sections_array = {
            "Announcement ID:": False,
            "Rating:": False,
            "References:         ": False,
            "Cross-References:": False,
            "CVSS scores:": False,
            "Affected Products:": False,
            "Description:": False,
            "Patch Instructions:": False,
            "Package List:": False,
            "References:": False,
        }
    
    # Process based on category
    space_index = 0
    block_index = 0
    is_description = False
    
    # Category-specific processing variables
    if catitemid == 202:
        desp_found_202 = False
    
    # Process each line
    for key, line in enumerate(arr):
        
        # OpenSUSE (202)
        if catitemid == 202:
            if "_____________________________" in line:
                continue
            if line == "":
                block_index += 1
                continue
            
            sections_array = idl_set_section(sections_array, line)
            
            if sections_array.get('Announcement ID:'):
                if 'announcement_id' not in final_arr:
                    final_arr['announcement_id'] = []
                if 'advisory_info' not in final_arr:
                    final_arr['advisory_info'] = []
                final_arr['announcement_id'].append(line)
                final_arr['advisory_info'].append(line)
            elif sections_array.get('Rating:'):
                if 'rating' not in final_arr:
                    final_arr['rating'] = []
                if 'advisory_info' not in final_arr:
                    final_arr['advisory_info'] = []
                final_arr['rating'].append(line)
                final_arr['advisory_info'].append(line)
            elif sections_array.get('References:         '):
                if 'references1' not in final_arr:
                    final_arr['references1'] = ['']
                if 'advisory_info' not in final_arr:
                    final_arr['advisory_info'] = []
                final_arr['references1'][0] += line + "\n"
                final_arr['advisory_info'].append(line)
            elif sections_array.get('Cross-References:'):
                if 'cross_references' not in final_arr:
                    final_arr['cross_references'] = ['']
                if 'advisory_info' not in final_arr:
                    final_arr['advisory_info'] = []
                final_arr['cross_references'][0] += line + "\n"
                final_arr['advisory_info'].append(line)
            elif sections_array.get('CVSS scores:'):
                if 'cvss_scores' not in final_arr:
                    final_arr['cvss_scores'] = ['']
                if 'advisory_info' not in final_arr:
                    final_arr['advisory_info'] = []
                final_arr['cvss_scores'][0] += line + "\n"
                final_arr['advisory_info'].append(line)
            elif sections_array.get('Affected Products:'):
                if 'affected_products' not in final_arr:
                    final_arr['affected_products'] = ['']
                if 'advisory_info' not in final_arr:
                    final_arr['advisory_info'] = []
                if "_____________________________" in line:
                    continue
                final_arr['affected_products'][0] += line + "\n"
                final_arr['advisory_info'].append(line)
            elif sections_array.get('Description:'):
                if 'description' not in final_arr:
                    final_arr['description'] = []
                final_arr['description'].append(line + "\n")
                desp_found_202 = True
            elif sections_array.get('Patch Instructions:'):
                if 'patch' not in final_arr:
                    final_arr['patch'] = []
                final_arr['patch'].append(line + "\n")
            elif sections_array.get('Package List:'):
                if 'list' not in final_arr:
                    final_arr['list'] = []
                final_arr['list'].append(line + "\n")
            elif sections_array.get('References:'):
                if 'references' not in final_arr:
                    final_arr['references'] = []
                final_arr['references'].append(line)
            else:
                if 'block' not in final_arr:
                    final_arr['block'] = {}
                if block_index not in final_arr['block']:
                    final_arr['block'][block_index] = ''
                if final_arr['block'][block_index] != '':
                    final_arr['block'][block_index] += " " + line
                else:
                    final_arr['block'][block_index] += line
        
        # Archlinux (198)
        elif catitemid == 198:
            flag = False
            flag2 = False
            summary = False
            description = False
            patch = False
            list_flag = False
            references = False
            impact = False
            
            if key == 0:
                final_arr['title'] = line
            if key == 1 and '======' in line:
                flag = True
            if key > 2 and 'Summary' in line:
                summary = True
            if key > 4 and 'Resolution' in line:
                patch = True
            if key > 6 and 'Workaround' in line:
                list_flag = True
            if key > 8 and 'Description' in line:
                description = True
            if key > 6 and 'Impact' in line:
                impact = True
            if key > 12 and 'References' in line:
                references = True
            
            if flag:
                if 'block1' not in final_arr:
                    final_arr['block1'] = {}
                final_arr['block1'][key] = line
            if summary:
                if 'summary' not in final_arr:
                    final_arr['summary'] = {}
                final_arr['summary'][key] = line
            if patch:
                if 'resolution' not in final_arr:
                    final_arr['resolution'] = {}
                if key not in final_arr['resolution']:
                    final_arr['resolution'][key] = ''
                final_arr['resolution'][key] += line + "\n"
            if list_flag:
                if 'workaround' not in final_arr:
                    final_arr['workaround'] = {}
                final_arr['workaround'][key] = line
            if description:
                if 'description' not in final_arr:
                    final_arr['description'] = {}
                if key not in final_arr['description']:
                    final_arr['description'][key] = ''
                final_arr['description'][key] += line + "\n"
            if references:
                if 'references' not in final_arr:
                    final_arr['references'] = {}
                if key not in final_arr['references']:
                    final_arr['references'][key] = ''
                final_arr['references'][key] += line + "\n"
            if impact:
                if 'impact' not in final_arr:
                    final_arr['impact'] = {}
                final_arr['impact'][key] = line
        
        # CentOS (199)
        elif catitemid == 199:
            updated_files = False
            source = False
            project = False
            twitter = False
            announce_mailing_list = False
            
            if key >= 5 and "The following updated files" in line:
                updated_files = True
            if "Source:" in line:
                source = True
                updated_files = False
            if "--" in line:
                project = True
                updated_files = False
                source = False
                continue
            if "Twitter:" in line:
                twitter = True
                updated_files = False
                source = False
                project = False
            if "______________" in line:
                announce_mailing_list = True
                updated_files = False
                source = False
                project = False
                twitter = False
                continue
            
            if key == 1:
                final_arr['severity'] = line
            if key == 3:
                final_arr['upstream_details'] = line
            if key >= 5 and updated_files:
                if 'updated_files' not in final_arr:
                    final_arr['updated_files'] = []
                final_arr['updated_files'].append(line + "\n")
            if source:
                if line == "":
                    continue
                if 'source' not in final_arr:
                    final_arr['source'] = []
                final_arr['source'].append(line + "\n")
            if project:
                if 'project' not in final_arr:
                    final_arr['project'] = []
                final_arr['project'].append(line)
            if twitter:
                if 'twitter' not in final_arr:
                    final_arr['twitter'] = []
                final_arr['twitter'].append(line)
            if announce_mailing_list:
                if 'announce_mailing_list' not in final_arr:
                    final_arr['announce_mailing_list'] = []
                final_arr['announce_mailing_list'].append(line)
        
        # DebianLTS (197)
        elif catitemid == 197:
            line = line.strip("\n").strip("\r")
            
            if "- --------------------------" in line:
                continue
            if line == "":
                block_index += 1
                space_index += 1
            
            if "Package        :" in line:
                final_arr['package'] = line
                if 'advisory_info' not in final_arr:
                    final_arr['advisory_info'] = []
                final_arr['advisory_info'].append(line)
            elif "Version        :" in line:
                final_arr['version'] = line
                if 'advisory_info' not in final_arr:
                    final_arr['advisory_info'] = []
                final_arr['advisory_info'].append(line)
            elif "CVE ID         :" in line:
                final_arr['cve_id'] = line
                if 'advisory_info' not in final_arr:
                    final_arr['advisory_info'] = []
                final_arr['advisory_info'].append(line)
            elif "Debian Bug     :" in line:
                final_arr['debian_bug'] = line
                if 'advisory_info' not in final_arr:
                    final_arr['advisory_info'] = []
                final_arr['advisory_info'].append(line)
                is_description = True
            elif is_description:
                if 'description' not in final_arr:
                    final_arr['description'] = []
                final_arr['description'].append(line + "\n")
            else:
                if 'block' not in final_arr:
                    final_arr['block'] = {}
                if block_index not in final_arr['block']:
                    final_arr['block'][block_index] = ''
                if final_arr['block'][block_index] != '':
                    final_arr['block'][block_index] += " " + line + "\n"
                else:
                    final_arr['block'][block_index] += line + "\n"
            
            if space_index >= 3:
                if 'original' not in final_arr:
                    final_arr['original'] = []
                final_arr['original'].append(line)
        
        # Debian (87)
        elif catitemid == 87:
            line = line.replace('Mailing list: debian-security-announce@lists.debian.org', '')
            
            if "- --------------------------" in line:
                continue
            if "---------------------------------------------------------------------------------" in line:
                break
            
            if line.strip() == "":
                space_index += 1
            
            if "Package        :" in line or ("Package" in line and " :" in line):
                final_arr['package'] = line
            elif "CVE ID         :" in line or ("CVE ID" in line and " :" in line):
                final_arr['cve_id'] = line
            elif "Debian Bug     :" in line:
                final_arr['debian_bug'] = line
                is_description = True
            elif is_description:
                if 'description' not in final_arr:
                    final_arr['description'] = []
                final_arr['description'].append(line + "\n")
            else:
                if 'block' not in final_arr:
                    final_arr['block'] = {}
                if block_index not in final_arr['block']:
                    final_arr['block'][block_index] = ''
                if final_arr['block'][block_index] != '':
                    final_arr['block'][block_index] += " " + line + "\n"
            
            if space_index >= 2 and space_index < 3:
                if "Package        :" in line or "CVE ID         :" in line:
                    if 'advisory_info' not in final_arr:
                        final_arr['advisory_info'] = []
                    final_arr['advisory_info'].append(line)
            
            if ('advisory_info' not in final_arr or len([x for x in final_arr.get('advisory_info', []) if x]) == 0) and 'package' in final_arr and 'cve_id' in final_arr:
                final_arr['advisory_info'] = ["", final_arr['package'], final_arr['cve_id']]
            
            if space_index >= 3:
                if 'original' not in final_arr:
                    final_arr['original'] = []
                final_arr['original'].append(line)
        
        # Fedora (89)
        elif catitemid == 89:
            if "-------------------------------------" in line or "_________________________" in line:
                continue
            if line == "":
                block_index += 1
            
            Desc = 0
            update_information = False
            ChangeLog = False
            References = False
            update_can_install = False
            all_packages_signed = False
            mailing_list = False
            
            if "Name        :" in line:
                final_arr['name'] = line
                if 'advisory_info' not in final_arr:
                    final_arr['advisory_info'] = []
                final_arr['advisory_info'].append(line)
            elif "Product     :" in line:
                final_arr['product'] = line
                if 'advisory_info' not in final_arr:
                    final_arr['advisory_info'] = []
                final_arr['advisory_info'].append(line)
            elif "Version     :" in line:
                final_arr['version'] = line
                if 'advisory_info' not in final_arr:
                    final_arr['advisory_info'] = []
                final_arr['advisory_info'].append(line)
            elif "Release     :" in line:
                final_arr['release'] = line
                if 'advisory_info' not in final_arr:
                    final_arr['advisory_info'] = []
                final_arr['advisory_info'].append(line)
            elif "URL         :" in line:
                final_arr['url'] = line
                if 'advisory_info' not in final_arr:
                    final_arr['advisory_info'] = []
                final_arr['advisory_info'].append(line)
            elif "Summary     :" in line:
                final_arr['summary'] = line
                if 'advisory_info' not in final_arr:
                    final_arr['advisory_info'] = []
                final_arr['advisory_info'].append(line)
            elif "Description :" in line:
                Desc = 1
            elif Desc and "Update Information:" not in line:
                if 'Description' not in final_arr:
                    final_arr['Description'] = []
                final_arr['Description'].append(line + "\n")
            elif "Update Information:" in line:
                Desc = 0
                update_information = True
            elif "ChangeLog:" in line:
                ChangeLog = True
                update_information = False
            elif "References:" in line:
                References = True
                ChangeLog = False
            elif "This update can be installed" in line:
                update_can_install = True
                References = False
            elif "All packages are signed" in line:
                all_packages_signed = True
                update_can_install = False
            elif "package-announce mailing list" in line:
                mailing_list = True
                all_packages_signed = False
            
            if update_information:
                if 'update_information' not in final_arr:
                    final_arr['update_information'] = []
                final_arr['update_information'].append(line + "\n")
            elif ChangeLog:
                if 'ChangeLog' not in final_arr:
                    final_arr['ChangeLog'] = []
                final_arr['ChangeLog'].append(line + "\n")
            elif References:
                if 'References' not in final_arr:
                    final_arr['References'] = []
                final_arr['References'].append(line + "\n")
            elif update_can_install:
                if 'update_can_install' not in final_arr:
                    final_arr['update_can_install'] = []
                final_arr['update_can_install'].append(line + "\n")
            elif all_packages_signed:
                if 'all_packages_signed' not in final_arr:
                    final_arr['all_packages_signed'] = []
                final_arr['all_packages_signed'].append(line)
            elif mailing_list:
                if 'mailing_list' not in final_arr:
                    final_arr['mailing_list'] = []
                final_arr['mailing_list'].append(line)
            else:
                if 'block' not in final_arr:
                    final_arr['block'] = {}
                if block_index not in final_arr['block']:
                    final_arr['block'][block_index] = ''
                if final_arr['block'][block_index] != '':
                    final_arr['block'][block_index] += " " + line + "\n"
                else:
                    final_arr['block'][block_index] += line + "\n"
    
    # Output the result as JSON
    print(json.dumps(final_arr, indent=2))
    
    cursor.close()
    db.close()


if __name__ == "__main__":
    main()


# Additional processing functions for remaining categories

def process_gentoo(arr, date_c, final_arr, sections_array):
    """Process Gentoo category (91)."""
    datefallback = "2010-01-01"
    fallback = date_c <= datefallback
    block_index = 0
    
    for key, line in enumerate(arr):
        if not fallback:
            if "- - - - - - - - - - - - - -" in line or "======" in line:
                continue
            if line == "":
                block_index += 1
            
            if "Severity:" in line:
                final_arr['serverity'] = line
                if 'advisory_info' not in final_arr:
                    final_arr['advisory_info'] = []
                final_arr['advisory_info'].append(line)
            elif "Title:" in line:
                final_arr['title'] = line
                if 'advisory_info' not in final_arr:
                    final_arr['advisory_info'] = []
                final_arr['advisory_info'].append(line)
            elif "Date:" in line:
                final_arr['date'] = line
                if 'advisory_info' not in final_arr:
                    final_arr['advisory_info'] = []
                final_arr['advisory_info'].append(line)
            elif "Bugs:" in line:
                final_arr['bugs'] = line
                if 'advisory_info' not in final_arr:
                    final_arr['advisory_info'] = []
                final_arr['advisory_info'].append(line)
            elif "ID:" in line:
                final_arr['id'] = line
                if 'advisory_info' not in final_arr:
                    final_arr['advisory_info'] = []
                final_arr['advisory_info'].append(line)
            else:
                sections_array = idl_set_section(sections_array, line)
            
            if sections_array.get('Synopsis'):
                if 'Synopsis' not in final_arr:
                    final_arr['Synopsis'] = []
                final_arr['Synopsis'].append(line + "\n")
            elif sections_array.get('Background'):
                if 'Background' not in final_arr:
                    final_arr['Background'] = []
                final_arr['Background'].append(line + "\n")
            elif sections_array.get('Affected packages'):
                if 'Affected_packages' not in final_arr:
                    final_arr['Affected_packages'] = []
                final_arr['Affected_packages'].append(line + "\n")
            elif sections_array.get('Description'):
                if 'Description' not in final_arr:
                    final_arr['Description'] = []
                final_arr['Description'].append(line + "\n")
            elif sections_array.get('Impact'):
                if 'Impact' not in final_arr:
                    final_arr['Impact'] = []
                final_arr['Impact'].append(line + "\n")
            elif sections_array.get('Workaround'):
                if 'Workaround' not in final_arr:
                    final_arr['Workaround'] = []
                final_arr['Workaround'].append(line + "\n")
            elif sections_array.get('Resolution'):
                if 'Resolution' not in final_arr:
                    final_arr['Resolution'] = []
                final_arr['Resolution'].append(line + "\n")
            elif sections_array.get('References'):
                if 'References' not in final_arr:
                    final_arr['References'] = []
                final_arr['References'].append(line + "\n")
            elif sections_array.get('Availability'):
                if 'Availability' not in final_arr:
                    final_arr['Availability'] = []
                final_arr['Availability'].append(line + "\n")
            elif sections_array.get('Concerns'):
                if 'Concerns' not in final_arr:
                    final_arr['Concerns'] = []
                final_arr['Concerns'].append(line + "\n")
            elif sections_array.get('License'):
                if 'License' not in final_arr:
                    final_arr['License'] = []
                final_arr['License'].append(line + "\n")
            else:
                if 'block' not in final_arr:
                    final_arr['block'] = {}
                if block_index not in final_arr['block']:
                    final_arr['block'][block_index] = ''
                if final_arr['block'][block_index] != '':
                    final_arr['block'][block_index] += " " + line
                else:
                    final_arr['block'][block_index] += line
        else:
            # Fallback processing
            if "- - - " in line or "- - - ----" in line:
                continue
            if line == "":
                block_index += 1
            
            if "PACKAGE :" in line:
                final_arr['serverity'] = line
                if 'advisory_info' not in final_arr:
                    final_arr['advisory_info'] = []
                final_arr['advisory_info'].append(line)
            elif "SUMMARY :" in line:
                final_arr['title'] = line
                if 'advisory_info' not in final_arr:
                    final_arr['advisory_info'] = []
                final_arr['advisory_info'].append(line)
            elif "DATE :" in line:
                final_arr['date'] = line
                if 'advisory_info' not in final_arr:
                    final_arr['advisory_info'] = []
                final_arr['advisory_info'].append(line)
            elif "EXPLOIT :" in line:
                final_arr['bugs'] = line
                if 'advisory_info' not in final_arr:
                    final_arr['advisory_info'] = []
                final_arr['advisory_info'].append(line)
            elif "VERSIONS AFFECTED :" in line:
                final_arr['id'] = line
                if 'advisory_info' not in final_arr:
                    final_arr['advisory_info'] = []
                final_arr['advisory_info'].append(line)
            elif "CVE :" in line:
                final_arr['id'] = line
                if 'advisory_info' not in final_arr:
                    final_arr['advisory_info'] = []
                final_arr['advisory_info'].append(line)
            else:
                if 'Description' not in final_arr:
                    final_arr['Description'] = []
                final_arr['Description'].append(line + "\n")
    
    return final_arr


def process_mageia(arr, final_arr, sections_array):
    """Process Mageia category (203)."""
    block_index = 0
    Ref = 0
    
    for key, line in enumerate(arr):
        if line == "":
            block_index += 1
        
        sections_array = idl_set_section(sections_array, line)
        
        if sections_array.get('Publication date:'):
            if 'publication_date' not in final_arr:
                final_arr['publication_date'] = []
            if 'advisory_info' not in final_arr:
                final_arr['advisory_info'] = []
            final_arr['publication_date'].append(line)
            final_arr['advisory_info'].append(line)
        elif sections_array.get('URL:'):
            if 'url' not in final_arr:
                final_arr['url'] = []
            if 'advisory_info' not in final_arr:
                final_arr['advisory_info'] = []
            final_arr['url'].append(line)
            final_arr['advisory_info'].append(line)
        elif sections_array.get('Type:'):
            if 'type' not in final_arr:
                final_arr['type'] = []
            if 'advisory_info' not in final_arr:
                final_arr['advisory_info'] = []
            final_arr['type'].append(line)
            final_arr['advisory_info'].append(line)
        elif sections_array.get('Affected Mageia releases:'):
            if 'affected_mageia_releases' not in final_arr:
                final_arr['affected_mageia_releases'] = []
            if 'advisory_info' not in final_arr:
                final_arr['advisory_info'] = []
            final_arr['affected_mageia_releases'].append(line + "\n")
            final_arr['advisory_info'].append(line)
        elif sections_array.get('CVE:'):
            if 'CVE' not in final_arr:
                final_arr['CVE'] = []
            if 'advisory_info' not in final_arr:
                final_arr['advisory_info'] = []
            if 'description' not in final_arr:
                final_arr['description'] = []
            final_arr['CVE'].append(line + "\n")
            final_arr['advisory_info'].append(line)
            final_arr['description'].append(line + "\n")
        elif sections_array.get('References:'):
            if 'references' not in final_arr:
                final_arr['references'] = []
            final_arr['references'].append(line)
            Ref = 1
        elif Ref and "SRPMS:" not in line:
            if 'references' not in final_arr:
                final_arr['references'] = []
            final_arr['references'].append(line)
        elif sections_array.get('SRPMS:'):
            Ref = 0
            if 'SRPMS' not in final_arr:
                final_arr['SRPMS'] = []
            if 'advisory_info' not in final_arr:
                final_arr['advisory_info'] = []
            final_arr['SRPMS'].append(line)
            final_arr['advisory_info'].append(line)
        else:
            if 'block' not in final_arr:
                final_arr['block'] = {}
            if block_index not in final_arr['block']:
                final_arr['block'][block_index] = ''
            if final_arr['block'][block_index] != '':
                final_arr['block'][block_index] += " " + line
            else:
                final_arr['block'][block_index] += line
    
    return final_arr


def process_oracle(arr, final_arr, sections_array):
    """Process Oracle category (217)."""
    block_index = 0
    tmp_flag = True
    
    for key, line in enumerate(arr):
        if line == "":
            block_index += 1
            tmp_flag = True
        
        if "________" in line:
            break
        
        sections_array = idl_set_section(sections_array, line)
        
        if sections_array.get('i386:'):
            if 'i386' not in final_arr:
                final_arr['i386'] = []
            final_arr['i386'].append(line + "\n")
        elif sections_array.get('SRPMS:'):
            if 'SRPMS' not in final_arr:
                final_arr['SRPMS'] = []
            final_arr['SRPMS'].append(line + "\n")
        elif sections_array.get('Related CVEs:'):
            if 'related_cves' not in final_arr:
                final_arr['related_cves'] = ['']
            if 'advisory_info' not in final_arr:
                final_arr['advisory_info'] = []
            final_arr['related_cves'][0] += line + "\n"
            final_arr['advisory_info'].append(line + "\n")
        elif sections_array.get('x86_64:'):
            if 'x86_64' not in final_arr:
                final_arr['x86_64'] = []
            final_arr['x86_64'].append(line + "\n")
        elif sections_array.get('aarch64:'):
            if 'aarch64' not in final_arr:
                final_arr['aarch64'] = []
            final_arr['aarch64'].append(line + "\n")
        elif sections_array.get('Description of changes:'):
            if 'advisory_info' not in final_arr:
                final_arr['advisory_info'] = []
            if 'description' not in final_arr:
                final_arr['description'] = []
            final_arr['advisory_info'].append(line)
            final_arr['description'].append(line + "\n")
        else:
            if 'block' not in final_arr:
                final_arr['block'] = {}
            if block_index not in final_arr['block']:
                final_arr['block'][block_index] = ''
            if final_arr['block'][block_index] != '':
                final_arr['block'][block_index] += " " + line
            else:
                final_arr['block'][block_index] += line
    
    return final_arr
