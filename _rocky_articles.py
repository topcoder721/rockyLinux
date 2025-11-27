import requests
import json
import hashlib
import time
from datetime import datetime
import mysql.connector
from mysql.connector import Error
import re
import os
from dotenv import load_dotenv

# Load environment variables from .env file
load_dotenv()

def call_fix(query=""):
    url = f'https://apollo.build.resf.org/v2/advisories/{query}'
    
    try:
        response = requests.get(
            url,
            timeout=30,
            allow_redirects=True
        )
        return response.text
    except Exception as e:
        print(f"Error in call_fix: {e}")
        return None


def get_request(url):
    try:
        response = requests.get(
            url,
            timeout=30,
            allow_redirects=True,
            verify=True
        )
        
        if response.status_code != 200:
            raise Exception(f"HTTP Error: {response.status_code}")
        
        return response.text
    except Exception as e:
        raise Exception(f"Error in get_request: {e}")


def get_db_connection():
    """
    Create and return database connection using environment variables.
    """
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


def check_webhash():
    new_hash = ""
    url = "https://apollo.build.resf.org/v2/advisories"
    
    try:
        response = get_request(url)
    except Exception as ex:
        print("Error with get request in checkwebhash function, rocky linux website may be down")
        return
    
    new_hash = hashlib.md5(response.encode()).hexdigest()
    final_date = datetime.now().strftime("%B %d, %Y")
    
    db = get_db_connection()
    if not db:
        return
    
    cursor = db.cursor(dictionary=True)
    
    try:
        cursor.execute("SELECT hash from rockyLinuxWebHash WHERE id = -1")
        rows = cursor.fetchall()
        
        for row in rows:
            current_hash = row['hash']
            if current_hash != new_hash:
                update_nulls = True
                
                published_date = datetime.strptime(final_date, "%B %d, %Y").strftime('%Y-%m-%d %H:%M:%S')
                
                update_query = """
                    UPDATE rockyLinuxWebHash 
                    SET hash = %s, date = %s, published_date = %s, identifier = %s, modified = %s
                    WHERE id = -1
                """
                cursor.execute(update_query, (new_hash, final_date, published_date, "web hash", final_date))
                db.commit()
    except Error as e:
        print(f"Error in check_webhash: {e}")
    finally:
        cursor.close()
        db.close()


def check_rocky():
    db = get_db_connection()
    if not db:
        return 1
    
    cursor = db.cursor(dictionary=True)
    
    url = "https://apollo.build.resf.org/v2/advisories"
    
    try:
        response = get_request(url)
    except Exception as ex:
        print("Error with get request in checkrocky function, rocky linux website may be down")
        cursor.close()
        db.close()
        return 1
    
    advis_data = json.loads(response)
    advisory_list = advis_data['advisories']
    full_date = datetime.now().strftime("%B %d, %Y")
    place_holder = int(time.time())
    true_clause = True
    false_clause = False
    update_case = True
    insert_new_case = True
    checker = 0
    
    state = 1
    asset_id = 0
    catid = 219
    access = 1
    created_by = 62
    created_by_alias = 'LinuxSecurity.com Team'
    modified = place_holder
    publish_up = place_holder
    created = place_holder
    language = '*'
    
    images = {
        "image_intro_alt": 'RockyLinux Distribution',
        "float_intro": "",
        "image_intro_caption": 'RockyLinux Distribution',
        "image_fulltext_caption": 'RockyLinux Distribution',
        "float_fulltext": "images/distros-large/rockylinux.png",
        "image_fulltext": "images/distros-large/rockylinux.png",
        "image_fulltext_alt": "'RockyLinux Distribution'",
        "image_intro": "images/distros-large/rockylinux.png"
    }
    images = json.dumps(images)
    
    attribs = {
        "helix_ultimate_image": "images/distros-large/rockylinux.png"
    }
    attribs = json.dumps(attribs)
    
    cursor.execute("SELECT id, hash, identifier from rockyLinuxWebHash")
    rows = cursor.fetchall()
    row_length = len(rows)
    
    for advisory in advisory_list:
        # temp variables that reset after each advisory
        title = ''
        new_title = ''
        list_a = []
        list_b = []
        full_text = ''
        alias = ''
        synopsis = ''
        rocky_insert1 = ''
        advis_tuple1 = ''
        count = 0
        cont = 0
        
        query = ""
        
        # fetch same as above
        for key, details in advisory.items():
            if key == "name":
                query = details
                title = 'Rocky Linux: ' + details
                final_hashed_title = hashlib.md5(title.encode()).hexdigest()
                title_tuple = [final_hashed_title, full_date, title, full_date]
        
        if len(advisory.get('fixes', [])) < 1:
            response = call_fix(query)
            if response:
                fixes = json.loads(response)
                advisory['fixes'] = fixes.get('advisory', {}).get('fixes', [])
        
        full_text = json.dumps(advisory)
        
        # if the hash is not the same but matches an already inserted title, meaning it changed then update the hash at that ID and identifier
        for row in rows:
            if row['hash'] != final_hashed_title:
                if row['identifier'] == title:
                    try:
                        update_case = True
                        update_query = """
                            UPDATE rockyLinuxWebHash 
                            SET hash = %s, modified = %s
                            WHERE identifier = %s
                        """
                        cursor.execute(update_query, (final_hashed_title, place_holder, title))
                        db.commit()
                    except Exception as ex:
                        print("Error updating rockyLinuxWebHash in check rocky function")
                else:
                    update_case = False
        
        # if hash is not in hash table, it keeps track of hash table length
        for row in rows:
            if row['identifier'] == title:
                cont += 1
            elif row['identifier'] != title:
                count += 1
        
        total_length = cont + count
        
        # if the length of the hash table is the same, then insert new hashed title
        if total_length == row_length and cont == 0:
            insert_new_case = True
            try:
                insert_query = """
                    INSERT INTO rockyLinuxWebHash (hash, date, identifier, modified)
                    VALUES (%s, %s, %s, %s)
                """
                cursor.execute(insert_query, (final_hashed_title, full_date, title, full_date))
                db.commit()
            except Exception as ex:
                print("Error inserting new advis into rockyLinuxWebHash in rockycheck function")
        elif total_length != row_length:
            insert_new_case = False
        
        for key, details in advisory.items():
            if update_case == True:
                if key == "publishedAt":
                    published_date = details
                    try:
                        update_query = """
                            UPDATE rockyLinuxWebHash 
                            SET published_date = %s
                            WHERE identifier = %s
                        """
                        cursor.execute(update_query, (published_date, title))
                        db.commit()
                    except Exception as ex:
                        print("Error inserting date into rockyLinuxWebHash in rockycheck function")
            
            alias = title.lower().replace(" ", "-").replace(":", "-").replace("--", "-")
            
            if key == "synopsis":
                synopsis = details
                list_a = details.split(":")
                if len(list_a) >= 2:
                    list_b = list_a[1].split(",")
                    new_title = title + " " + list_b[0]
                elif len(list_a) < 2:
                    list_b = list_a[0].split(",")
                    new_title = title + " " + list_b[0]
        
        # set articles publish date and created date in article
        publish_up = advisory['publishedAt'][:19].replace("T", " ")
        created = advisory['publishedAt'][:19].replace("T", " ")
        created1 = advisory['publishedAt'][:19].split("T")
        created1 = created1[1]
        
        alias = alias.replace("rocky-linux", f"rocky-linux-{list_b[0].strip()}") + '-' + created1
        
        # Phrases to remove
        remove = [
            'bug fix',
            'enhancement',
            'bug fix and enhancement update',
            'update',
            'security update'
        ]
        
        # Remove each phrase
        for phrase in remove:
            alias = alias.replace(phrase.lower(), '')
        
        alias = alias.lower().replace(" ", "-").replace(":", "-").replace("--", "-")
        alias = re.sub(r'-+', '-', alias)  # replace multiple dashes with one
        alias = alias.replace('.', '')
        alias = alias.strip(".")
        
        new_title = new_title + ' Security Advisories Updates'
        
        # get the article if already exist in Joomla content and webhash failed to get
        cursor.execute("SELECT * from #__content where catid = 219 AND `title` = %s", (new_title,))
        articles = cursor.fetchall()
        
        if len(articles):
            insert_new_case = False
            update_case = True
        else:
            insert_new_case = True
            update_case = False
        
        if "RLBA" in new_title:
            continue
        
        # update already inserted title
        if update_case == True:
            try:
                if isinstance(modified, int):
                    modified = datetime.fromtimestamp(modified).strftime('%Y-%m-%d %H:%M:%S')
                
                update_query = """
                    UPDATE #__content 
                    SET title = %s, alias = %s, fulltext = %s, introtext = %s,
                        asset_id = %s, state = %s, catid = %s, created = %s,
                        created_by = %s, created_by_alias = %s, modified = %s,
                        publish_up = %s, language = %s, images = %s, attribs = %s, access = %s
                    WHERE title = %s
                """
                cursor.execute(update_query, (
                    new_title, alias, full_text, synopsis,
                    asset_id, state, catid, created,
                    created_by, created_by_alias, modified,
                    publish_up, language, images, attribs, access,
                    new_title
                ))
                db.commit()
            except Exception as ex:
                print("Error updating xu5gc content, potentially no updates to current titles or something is broken in rockycheck function")
        
        elif insert_new_case == True:
            try:
                if isinstance(modified, int):
                    modified = datetime.fromtimestamp(modified).strftime('%Y-%m-%d %H:%M:%S')
                
                insert_query = """
                    INSERT INTO #__content 
                    (title, alias, fulltext, introtext, asset_id, state, catid, created,
                     created_by, created_by_alias, modified, publish_up, language, images, attribs, access)
                    VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
                """
                cursor.execute(insert_query, (
                    new_title, alias, full_text, synopsis, asset_id, state, catid, created,
                    created_by, created_by_alias, modified, publish_up, language, images, attribs, access
                ))
                db.commit()
                
                obj_id = cursor.lastrowid
                
                workflow_sql = """
                    INSERT INTO #__workflow_associations (item_id, stage_id, extension) 
                    SELECT c.id as item_id, '1', 'com_content.article' FROM #__content AS c 
                    WHERE c.id = %s
                """
                cursor.execute(workflow_sql, (obj_id,))
                db.commit()
            except Exception as ex:
                print("Error inserting new advisory into xu5gc content in rockycheck function")
    
    # Final workflow associations insert
    try:
        workflow_sql = """
            INSERT INTO xu5gc_workflow_associations (item_id, stage_id, extension) 
            SELECT c.id as item_id, '1', 'com_content.article' FROM xu5gc_content AS c 
            WHERE NOT EXISTS (SELECT wa.item_id FROM xu5gc_workflow_associations AS wa WHERE wa.item_id = c.id)
        """
        cursor.execute(workflow_sql)
        db.commit()
    except Exception as ex:
        print(f"Error in final workflow associations: {ex}")
    
    cursor.close()
    db.close()
    
    if update_case == True or insert_new_case == True:
        return 0
    elif update_case == False or insert_new_case == False:
        return 1


if __name__ == "__main__":
    check_webhash()
    ret = check_rocky()
    
    if ret == 0:
        print("Changes made, database updated")
    elif ret == 1:
        print("No changes made")
