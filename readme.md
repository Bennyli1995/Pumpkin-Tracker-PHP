This is the readme doc for the 304 project!

our repo location:
https://github.students.cs.ubc.ca/CPSC304-2023W-T1/project_a2v5h_e0p8y_y7v1z

**Summary of Project:**  
The pumpkin patch application is an agricultural management tool to track and manage the necessary aspects of a patch, including pumpkin variety, visitor management, and marketing events. The application can be used by owners, farmers, visitors, government and regulatory bodies, educational institutions, etc. to manage pumpkin patches across the region.

Some useful docs to help get set up:
https://www.students.cs.ubc.ca/~cs-304/resources/sql-plus-resources/sql-plus-setup.html
https://www.students.cs.ubc.ca/~cs-304/resources.html

We will be using PHP and Oracle for our application.

To get started:

1. ssh into the UBC servers
2. in the root file of the project, run the following command:
   sh ./remote-start.sh
3. you may need to set up your UBC Oracle DBMS. Refer to this:
   https://www.students.cs.ubc.ca/~cs-304/resources/sql-plus-resources/sql-plus-setup.html

I suggest using VS Code / IntelliJ as your editor of choice.

You must transfer all files to the ubc grad apache servers: run the command below:

rsync -avz project_a2v5h_e0p8y_y7v1z/ CWLusername@remote.students.cs.ubc.ca:~/public_html/

.ENV
You must create a .env file in order for Oracle access. Please refer to .env.example

Run these two commands to give permission for the server to render pages (in order). This assumes that you are in the public/html directory:

chmod +x utils/change_permissions.sh
./utils/change_permissions.sh

to copy from remote to our repo:
scp -r cli66@remote.students.cs.ubc.ca:~/public_html/\* /home/c/cli66/cpsc_304/project_a2v5h_e0p8y_y7v1z/

the other way around (our repo to remote server):
scp -r \* cli66@remote.students.cs.ubc.ca:~/public_html/

## activating your database
- For more details please refer to the documentation below:
https://www.students.cs.ubc.ca/~cs-304/resources/sql-plus-resources/CPSC_304_Software_Guide.pdf
1) In your terminal, type the following command: sqlplus ora_YOURCWL@stu
2) Your password is ’a’ followed by your student number (Sally, with CWL ’notbob’ and student number 12345 would log in with: ‘sqlplus ora notbob@stu‘ and ‘a12345‘)
3) to run the file (init_database.sql), type the following command: start init_database.sql
