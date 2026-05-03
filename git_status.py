import subprocess

try:
    result = subprocess.run(["git", "status"], cwd="c:/Unizar/2ºcuatri/PS/educattio", capture_output=True, text=True)
    print("STDOUT:", result.stdout)
    print("STDERR:", result.stderr)
except Exception as e:
    print("Exception:", e)
